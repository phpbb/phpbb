<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Dumper;

use Symfony\Component\Yaml\Dumper as YmlDumper;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * YamlDumper dumps a service container as a YAML string.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class YamlDumper extends Dumper
{
    private $dumper;

    /**
     * Dumps the service container as an YAML string.
     *
     * @return string A YAML string representing of the service container
     */
    public function dump(array $options = array())
    {
        if (!class_exists('Symfony\Component\Yaml\Dumper')) {
            throw new RuntimeException('Unable to dump the container as the Symfony Yaml Component is not installed.');
        }

        if (null === $this->dumper) {
            $this->dumper = new YmlDumper();
        }

        return $this->addParameters()."\n".$this->addServices();
    }

    /**
     * Adds a service.
     *
     * @param string     $id
     * @param Definition $definition
     *
     * @return string
     */
    private function addService($id, Definition $definition)
    {
        $code = "    $id:\n";
        if ($class = $definition->getClass()) {
            if ('\\' === substr($class, 0, 1)) {
                $class = substr($class, 1);
            }

            $code .= sprintf("        class: %s\n", $this->dumper->dump($class));
        }

        if (!$definition->isPublic()) {
            $code .= "        public: false\n";
        }

        $tagsCode = '';
        foreach ($definition->getTags() as $name => $tags) {
            foreach ($tags as $attributes) {
                $att = array();
                foreach ($attributes as $key => $value) {
                    $att[] = sprintf('%s: %s', $this->dumper->dump($key), $this->dumper->dump($value));
                }
                $att = $att ? ', '.implode(', ', $att) : '';

                $tagsCode .= sprintf("            - { name: %s%s }\n", $this->dumper->dump($name), $att);
            }
        }
        if ($tagsCode) {
            $code .= "        tags:\n".$tagsCode;
        }

        if ($definition->getFile()) {
            $code .= sprintf("        file: %s\n", $this->dumper->dump($definition->getFile()));
        }

        if ($definition->isSynthetic()) {
            $code .= "        synthetic: true\n";
        }

        if ($definition->isSynchronized(false)) {
            $code .= "        synchronized: true\n";
        }

        if ($definition->isDeprecated()) {
            $code .= sprintf("        deprecated: %s\n", $definition->getDeprecationMessage('%service_id%'));
        }

        if ($definition->isAutowired()) {
            $code .= "        autowire: true\n";
        }

        $autowiringTypesCode = '';
        foreach ($definition->getAutowiringTypes() as $autowiringType) {
            $autowiringTypesCode .= sprintf("            - %s\n", $this->dumper->dump($autowiringType));
        }
        if ($autowiringTypesCode) {
            $code .= sprintf("        autowiring_types:\n%s", $autowiringTypesCode);
        }

        if ($definition->getFactoryClass(false)) {
            $code .= sprintf("        factory_class: %s\n", $this->dumper->dump($definition->getFactoryClass(false)));
        }

        if ($definition->isAbstract()) {
            $code .= "        abstract: true\n";
        }

        if ($definition->isLazy()) {
            $code .= "        lazy: true\n";
        }

        if ($definition->getFactoryMethod(false)) {
            $code .= sprintf("        factory_method: %s\n", $this->dumper->dump($definition->getFactoryMethod(false)));
        }

        if ($definition->getFactoryService(false)) {
            $code .= sprintf("        factory_service: %s\n", $this->dumper->dump($definition->getFactoryService(false)));
        }

        if ($definition->getArguments()) {
            $code .= sprintf("        arguments: %s\n", $this->dumper->dump($this->dumpValue($definition->getArguments()), 0));
        }

        if ($definition->getProperties()) {
            $code .= sprintf("        properties: %s\n", $this->dumper->dump($this->dumpValue($definition->getProperties()), 0));
        }

        if ($definition->getMethodCalls()) {
            $code .= sprintf("        calls:\n%s\n", $this->dumper->dump($this->dumpValue($definition->getMethodCalls()), 1, 12));
        }

        if (!$definition->isShared()) {
            $code .= "        shared: false\n";
        }

        if (ContainerInterface::SCOPE_CONTAINER !== $scope = $definition->getScope(false)) {
            $code .= sprintf("        scope: %s\n", $this->dumper->dump($scope));
        }

        if (null !== $decorated = $definition->getDecoratedService()) {
            list($decorated, $renamedId, $priority) = $decorated;
            $code .= sprintf("        decorates: %s\n", $decorated);
            if (null !== $renamedId) {
                $code .= sprintf("        decoration_inner_name: %s\n", $renamedId);
            }
            if (0 !== $priority) {
                $code .= sprintf("        decoration_priority: %s\n", $priority);
            }
        }

        if ($callable = $definition->getFactory()) {
            $code .= sprintf("        factory: %s\n", $this->dumper->dump($this->dumpCallable($callable), 0));
        }

        if ($callable = $definition->getConfigurator()) {
            $code .= sprintf("        configurator: %s\n", $this->dumper->dump($this->dumpCallable($callable), 0));
        }

        return $code;
    }

    /**
     * Adds a service alias.
     *
     * @param string $alias
     * @param Alias  $id
     *
     * @return string
     */
    private function addServiceAlias($alias, Alias $id)
    {
        if ($id->isPublic()) {
            return sprintf("    %s: '@%s'\n", $alias, $id);
        }

        return sprintf("    %s:\n        alias: %s\n        public: false\n", $alias, $id);
    }

    /**
     * Adds services.
     *
     * @return string
     */
    private function addServices()
    {
        if (!$this->container->getDefinitions()) {
            return '';
        }

        $code = "services:\n";
        foreach ($this->container->getDefinitions() as $id => $definition) {
            $code .= $this->addService($id, $definition);
        }

        $aliases = $this->container->getAliases();
        foreach ($aliases as $alias => $id) {
            while (isset($aliases[(string) $id])) {
                $id = $aliases[(string) $id];
            }
            $code .= $this->addServiceAlias($alias, $id);
        }

        return $code;
    }

    /**
     * Adds parameters.
     *
     * @return string
     */
    private function addParameters()
    {
        if (!$this->container->getParameterBag()->all()) {
            return '';
        }

        $parameters = $this->prepareParameters($this->container->getParameterBag()->all(), $this->container->isFrozen());

        return $this->dumper->dump(array('parameters' => $parameters), 2);
    }

    /**
     * Dumps callable to YAML format.
     *
     * @param callable $callable
     *
     * @return callable
     */
    private function dumpCallable($callable)
    {
        if (is_array($callable)) {
            if ($callable[0] instanceof Reference) {
                $callable = array($this->getServiceCall((string) $callable[0], $callable[0]), $callable[1]);
            } else {
                $callable = array($callable[0], $callable[1]);
            }
        }

        return $callable;
    }

    /**
     * Dumps the value to YAML format.
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws RuntimeException When trying to dump object or resource
     */
    private function dumpValue($value)
    {
        if (is_array($value)) {
            $code = array();
            foreach ($value as $k => $v) {
                $code[$k] = $this->dumpValue($v);
            }

            return $code;
        } elseif ($value instanceof Reference) {
            return $this->getServiceCall((string) $value, $value);
        } elseif ($value instanceof Parameter) {
            return $this->getParameterCall((string) $value);
        } elseif ($value instanceof Expression) {
            return $this->getExpressionCall((string) $value);
        } elseif (is_object($value) || is_resource($value)) {
            throw new RuntimeException('Unable to dump a service container if a parameter is an object or a resource.');
        }

        return $value;
    }

    /**
     * Gets the service call.
     *
     * @param string    $id
     * @param Reference $reference
     *
     * @return string
     */
    private function getServiceCall($id, Reference $reference = null)
    {
        if (null !== $reference && ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE !== $reference->getInvalidBehavior()) {
            return sprintf('@?%s', $id);
        }

        return sprintf('@%s', $id);
    }

    /**
     * Gets parameter call.
     *
     * @param string $id
     *
     * @return string
     */
    private function getParameterCall($id)
    {
        return sprintf('%%%s%%', $id);
    }

    private function getExpressionCall($expression)
    {
        return sprintf('@=%s', $expression);
    }

    /**
     * Prepares parameters.
     *
     * @param array $parameters
     * @param bool  $escape
     *
     * @return array
     */
    private function prepareParameters(array $parameters, $escape = true)
    {
        $filtered = array();
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $value = $this->prepareParameters($value, $escape);
            } elseif ($value instanceof Reference || is_string($value) && 0 === strpos($value, '@')) {
                $value = '@'.$value;
            }

            $filtered[$key] = $value;
        }

        return $escape ? $this->escape($filtered) : $filtered;
    }

    /**
     * Escapes arguments.
     *
     * @return array
     */
    private function escape(array $arguments)
    {
        $args = array();
        foreach ($arguments as $k => $v) {
            if (is_array($v)) {
                $args[$k] = $this->escape($v);
            } elseif (is_string($v)) {
                $args[$k] = str_replace('%', '%%', $v);
            } else {
                $args[$k] = $v;
            }
        }

        return $args;
    }
}
