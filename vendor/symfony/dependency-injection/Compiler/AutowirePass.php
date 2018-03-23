<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Guesses constructor arguments of services definitions and try to instantiate services if necessary.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AutowirePass implements CompilerPassInterface
{
    private $container;
    private $reflectionClasses = array();
    private $definedTypes = array();
    private $types;
    private $notGuessableTypes = array();
    private $autowired = array();

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $throwingAutoloader = function ($class) { throw new \ReflectionException(sprintf('Class %s does not exist', $class)); };
        spl_autoload_register($throwingAutoloader);

        try {
            $this->container = $container;
            foreach ($container->getDefinitions() as $id => $definition) {
                if ($definition->isAutowired()) {
                    $this->completeDefinition($id, $definition);
                }
            }
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        }

        spl_autoload_unregister($throwingAutoloader);

        // Free memory and remove circular reference to container
        $this->container = null;
        $this->reflectionClasses = array();
        $this->definedTypes = array();
        $this->types = null;
        $this->notGuessableTypes = array();
        $this->autowired = array();

        if (isset($e)) {
            throw $e;
        }
    }

    /**
     * Wires the given definition.
     *
     * @param string     $id
     * @param Definition $definition
     *
     * @throws RuntimeException
     */
    private function completeDefinition($id, Definition $definition)
    {
        if ($definition->getFactory() || $definition->getFactoryClass(false) || $definition->getFactoryService(false)) {
            throw new RuntimeException(sprintf('Service "%s" can use either autowiring or a factory, not both.', $id));
        }

        if (!$reflectionClass = $this->getReflectionClass($id, $definition)) {
            return;
        }

        $this->container->addClassResource($reflectionClass);

        if (!$constructor = $reflectionClass->getConstructor()) {
            return;
        }
        $parameters = $constructor->getParameters();
        if (method_exists('ReflectionMethod', 'isVariadic') && $constructor->isVariadic()) {
            array_pop($parameters);
        }

        $arguments = $definition->getArguments();
        foreach ($parameters as $index => $parameter) {
            if (array_key_exists($index, $arguments) && '' !== $arguments[$index]) {
                continue;
            }

            try {
                if (!$typeHint = $parameter->getClass()) {
                    if (isset($arguments[$index])) {
                        continue;
                    }

                    // no default value? Then fail
                    if (!$parameter->isOptional()) {
                        throw new RuntimeException(sprintf('Unable to autowire argument index %d ($%s) for the service "%s". If this is an object, give it a type-hint. Otherwise, specify this argument\'s value explicitly.', $index, $parameter->name, $id));
                    }

                    // specifically pass the default value
                    $arguments[$index] = $parameter->getDefaultValue();

                    continue;
                }

                if (isset($this->autowired[$typeHint->name])) {
                    $arguments[$index] = $this->autowired[$typeHint->name] ? new Reference($this->autowired[$typeHint->name]) : null;
                    continue;
                }

                if (null === $this->types) {
                    $this->populateAvailableTypes();
                }

                if (isset($this->types[$typeHint->name]) && !isset($this->notGuessableTypes[$typeHint->name])) {
                    $value = new Reference($this->types[$typeHint->name]);
                } else {
                    try {
                        $value = $this->createAutowiredDefinition($typeHint, $id);
                    } catch (RuntimeException $e) {
                        if ($parameter->isDefaultValueAvailable()) {
                            $value = $parameter->getDefaultValue();
                        } elseif ($parameter->allowsNull()) {
                            $value = null;
                        } else {
                            throw $e;
                        }
                        $this->autowired[$typeHint->name] = false;
                    }
                }
            } catch (\ReflectionException $e) {
                // Typehint against a non-existing class

                if (!$parameter->isDefaultValueAvailable()) {
                    throw new RuntimeException(sprintf('Cannot autowire argument %s for %s because the type-hinted class does not exist (%s).', $index + 1, $definition->getClass(), $e->getMessage()), 0, $e);
                }

                $value = $parameter->getDefaultValue();
            }

            $arguments[$index] = $value;
        }

        if ($parameters && !isset($arguments[++$index])) {
            while (0 <= --$index) {
                $parameter = $parameters[$index];
                if (!$parameter->isDefaultValueAvailable() || $parameter->getDefaultValue() !== $arguments[$index]) {
                    break;
                }
                unset($arguments[$index]);
            }
        }

        // it's possible index 1 was set, then index 0, then 2, etc
        // make sure that we re-order so they're injected as expected
        ksort($arguments);
        $definition->setArguments($arguments);
    }

    /**
     * Populates the list of available types.
     */
    private function populateAvailableTypes()
    {
        $this->types = array();

        foreach ($this->container->getDefinitions() as $id => $definition) {
            $this->populateAvailableType($id, $definition);
        }
    }

    /**
     * Populates the list of available types for a given definition.
     *
     * @param string     $id
     * @param Definition $definition
     */
    private function populateAvailableType($id, Definition $definition)
    {
        // Never use abstract services
        if ($definition->isAbstract()) {
            return;
        }

        foreach ($definition->getAutowiringTypes() as $type) {
            $this->definedTypes[$type] = true;
            $this->types[$type] = $id;
            unset($this->notGuessableTypes[$type]);
        }

        if (!$reflectionClass = $this->getReflectionClass($id, $definition)) {
            return;
        }

        foreach ($reflectionClass->getInterfaces() as $reflectionInterface) {
            $this->set($reflectionInterface->name, $id);
        }

        do {
            $this->set($reflectionClass->name, $id);
        } while ($reflectionClass = $reflectionClass->getParentClass());
    }

    /**
     * Associates a type and a service id if applicable.
     *
     * @param string $type
     * @param string $id
     */
    private function set($type, $id)
    {
        if (isset($this->definedTypes[$type])) {
            return;
        }

        if (!isset($this->types[$type])) {
            $this->types[$type] = $id;

            return;
        }

        if ($this->types[$type] === $id) {
            return;
        }

        if (!isset($this->notGuessableTypes[$type])) {
            $this->notGuessableTypes[$type] = true;
            $this->types[$type] = (array) $this->types[$type];
        }

        $this->types[$type][] = $id;
    }

    /**
     * Registers a definition for the type if possible or throws an exception.
     *
     * @param \ReflectionClass $typeHint
     * @param string           $id
     *
     * @return Reference A reference to the registered definition
     *
     * @throws RuntimeException
     */
    private function createAutowiredDefinition(\ReflectionClass $typeHint, $id)
    {
        if (isset($this->notGuessableTypes[$typeHint->name])) {
            $classOrInterface = $typeHint->isInterface() ? 'interface' : 'class';
            $matchingServices = implode(', ', $this->types[$typeHint->name]);

            throw new RuntimeException(sprintf('Unable to autowire argument of type "%s" for the service "%s". Multiple services exist for this %s (%s).', $typeHint->name, $id, $classOrInterface, $matchingServices));
        }

        if (!$typeHint->isInstantiable()) {
            $classOrInterface = $typeHint->isInterface() ? 'interface' : 'class';
            throw new RuntimeException(sprintf('Unable to autowire argument of type "%s" for the service "%s". No services were found matching this %s and it cannot be auto-registered.', $typeHint->name, $id, $classOrInterface));
        }

        $this->autowired[$typeHint->name] = $argumentId = sprintf('autowired.%s', $typeHint->name);

        $argumentDefinition = $this->container->register($argumentId, $typeHint->name);
        $argumentDefinition->setPublic(false);

        try {
            $this->completeDefinition($argumentId, $argumentDefinition);
        } catch (RuntimeException $e) {
            $classOrInterface = $typeHint->isInterface() ? 'interface' : 'class';
            $message = sprintf('Unable to autowire argument of type "%s" for the service "%s". No services were found matching this %s and it cannot be auto-registered.', $typeHint->name, $id, $classOrInterface);
            throw new RuntimeException($message, 0, $e);
        }

        return new Reference($argumentId);
    }

    /**
     * Retrieves the reflection class associated with the given service.
     *
     * @param string     $id
     * @param Definition $definition
     *
     * @return \ReflectionClass|false
     */
    private function getReflectionClass($id, Definition $definition)
    {
        if (isset($this->reflectionClasses[$id])) {
            return $this->reflectionClasses[$id];
        }

        // Cannot use reflection if the class isn't set
        if (!$class = $definition->getClass()) {
            return false;
        }

        $class = $this->container->getParameterBag()->resolveValue($class);

        if ($deprecated = $definition->isDeprecated()) {
            $prevErrorHandler = set_error_handler(function ($level, $message, $file, $line) use (&$prevErrorHandler) {
                return (E_USER_DEPRECATED === $level || !$prevErrorHandler) ? false : $prevErrorHandler($level, $message, $file, $line);
            });
        }

        $e = null;

        try {
            $reflector = new \ReflectionClass($class);
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        }

        if ($deprecated) {
            restore_error_handler();
        }

        if (null !== $e) {
            if (!$e instanceof \ReflectionException) {
                throw $e;
            }
            $reflector = false;
        }

        return $this->reflectionClasses[$id] = $reflector;
    }
}
