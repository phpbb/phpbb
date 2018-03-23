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

use Symfony\Component\DependencyInjection\Variable;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\DumperInterface as ProxyDumper;
use Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\NullDumper;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * PhpDumper dumps a service container as a PHP class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PhpDumper extends Dumper
{
    /**
     * Characters that might appear in the generated variable name as first character.
     */
    const FIRST_CHARS = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * Characters that might appear in the generated variable name as any but the first character.
     */
    const NON_FIRST_CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789_';

    private $inlinedDefinitions;
    private $definitionVariables;
    private $referenceVariables;
    private $variableCount;
    private $reservedVariables = array('instance', 'class');
    private $expressionLanguage;
    private $targetDirRegex;
    private $targetDirMaxMatches;
    private $docStar;

    /**
     * @var ExpressionFunctionProviderInterface[]
     */
    private $expressionLanguageProviders = array();

    /**
     * @var ProxyDumper
     */
    private $proxyDumper;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContainerBuilder $container)
    {
        parent::__construct($container);

        $this->inlinedDefinitions = new \SplObjectStorage();
    }

    /**
     * Sets the dumper to be used when dumping proxies in the generated container.
     */
    public function setProxyDumper(ProxyDumper $proxyDumper)
    {
        $this->proxyDumper = $proxyDumper;
    }

    /**
     * Dumps the service container as a PHP class.
     *
     * Available options:
     *
     *  * class:      The class name
     *  * base_class: The base class name
     *  * namespace:  The class namespace
     *
     * @return string A PHP class representing of the service container
     */
    public function dump(array $options = array())
    {
        $this->targetDirRegex = null;
        $options = array_merge(array(
            'class' => 'ProjectServiceContainer',
            'base_class' => 'Container',
            'namespace' => '',
            'debug' => true,
        ), $options);
        $this->docStar = $options['debug'] ? '*' : '';

        if (!empty($options['file']) && is_dir($dir = dirname($options['file']))) {
            // Build a regexp where the first root dirs are mandatory,
            // but every other sub-dir is optional up to the full path in $dir
            // Mandate at least 2 root dirs and not more that 5 optional dirs.

            $dir = explode(DIRECTORY_SEPARATOR, realpath($dir));
            $i = count($dir);

            if (3 <= $i) {
                $regex = '';
                $lastOptionalDir = $i > 8 ? $i - 5 : 3;
                $this->targetDirMaxMatches = $i - $lastOptionalDir;

                while (--$i >= $lastOptionalDir) {
                    $regex = sprintf('(%s%s)?', preg_quote(DIRECTORY_SEPARATOR.$dir[$i], '#'), $regex);
                }

                do {
                    $regex = preg_quote(DIRECTORY_SEPARATOR.$dir[$i], '#').$regex;
                } while (0 < --$i);

                $this->targetDirRegex = '#'.preg_quote($dir[0], '#').$regex.'#';
            }
        }

        $code = $this->startClass($options['class'], $options['base_class'], $options['namespace']);

        if ($this->container->isFrozen()) {
            $code .= $this->addFrozenConstructor();
            $code .= $this->addFrozenCompile();
            $code .= $this->addIsFrozenMethod();
        } else {
            $code .= $this->addConstructor();
        }

        $code .=
            $this->addServices().
            $this->addDefaultParametersMethod().
            $this->endClass().
            $this->addProxyClasses()
        ;
        $this->targetDirRegex = null;

        return $code;
    }

    /**
     * Retrieves the currently set proxy dumper or instantiates one.
     *
     * @return ProxyDumper
     */
    private function getProxyDumper()
    {
        if (!$this->proxyDumper) {
            $this->proxyDumper = new NullDumper();
        }

        return $this->proxyDumper;
    }

    /**
     * Generates Service local temp variables.
     *
     * @param string $cId
     * @param string $definition
     *
     * @return string
     */
    private function addServiceLocalTempVariables($cId, $definition)
    {
        static $template = "        \$%s = %s;\n";

        $localDefinitions = array_merge(
            array($definition),
            $this->getInlinedDefinitions($definition)
        );

        $calls = $behavior = array();
        foreach ($localDefinitions as $iDefinition) {
            $this->getServiceCallsFromArguments($iDefinition->getArguments(), $calls, $behavior);
            $this->getServiceCallsFromArguments($iDefinition->getMethodCalls(), $calls, $behavior);
            $this->getServiceCallsFromArguments($iDefinition->getProperties(), $calls, $behavior);
            $this->getServiceCallsFromArguments(array($iDefinition->getConfigurator()), $calls, $behavior);
            $this->getServiceCallsFromArguments(array($iDefinition->getFactory()), $calls, $behavior);
        }

        $code = '';
        foreach ($calls as $id => $callCount) {
            if ('service_container' === $id || $id === $cId) {
                continue;
            }

            if ($callCount > 1) {
                $name = $this->getNextVariableName();
                $this->referenceVariables[$id] = new Variable($name);

                if (ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE === $behavior[$id]) {
                    $code .= sprintf($template, $name, $this->getServiceCall($id));
                } else {
                    $code .= sprintf($template, $name, $this->getServiceCall($id, new Reference($id, ContainerInterface::NULL_ON_INVALID_REFERENCE)));
                }
            }
        }

        if ('' !== $code) {
            $code .= "\n";
        }

        return $code;
    }

    /**
     * Generates code for the proxies to be attached after the container class.
     *
     * @return string
     */
    private function addProxyClasses()
    {
        /* @var $definitions Definition[] */
        $definitions = array_filter(
            $this->container->getDefinitions(),
            array($this->getProxyDumper(), 'isProxyCandidate')
        );
        $code = '';
        $strip = '' === $this->docStar && method_exists('Symfony\Component\HttpKernel\Kernel', 'stripComments');

        foreach ($definitions as $definition) {
            $proxyCode = "\n".$this->getProxyDumper()->getProxyCode($definition);
            if ($strip) {
                $proxyCode = "<?php\n".$proxyCode;
                $proxyCode = substr(Kernel::stripComments($proxyCode), 5);
            }
            $code .= $proxyCode;
        }

        return $code;
    }

    /**
     * Generates the require_once statement for service includes.
     *
     * @return string
     */
    private function addServiceInclude(Definition $definition)
    {
        $template = "        require_once %s;\n";
        $code = '';

        if (null !== $file = $definition->getFile()) {
            $code .= sprintf($template, $this->dumpValue($file));
        }

        foreach ($this->getInlinedDefinitions($definition) as $definition) {
            if (null !== $file = $definition->getFile()) {
                $code .= sprintf($template, $this->dumpValue($file));
            }
        }

        if ('' !== $code) {
            $code .= "\n";
        }

        return $code;
    }

    /**
     * Generates the inline definition of a service.
     *
     * @param string     $id
     * @param Definition $definition
     *
     * @return string
     *
     * @throws RuntimeException                  When the factory definition is incomplete
     * @throws ServiceCircularReferenceException When a circular reference is detected
     */
    private function addServiceInlinedDefinitions($id, Definition $definition)
    {
        $code = '';
        $variableMap = $this->definitionVariables;
        $nbOccurrences = new \SplObjectStorage();
        $processed = new \SplObjectStorage();
        $inlinedDefinitions = $this->getInlinedDefinitions($definition);

        foreach ($inlinedDefinitions as $definition) {
            if (false === $nbOccurrences->contains($definition)) {
                $nbOccurrences->offsetSet($definition, 1);
            } else {
                $i = $nbOccurrences->offsetGet($definition);
                $nbOccurrences->offsetSet($definition, $i + 1);
            }
        }

        foreach ($inlinedDefinitions as $sDefinition) {
            if ($processed->contains($sDefinition)) {
                continue;
            }
            $processed->offsetSet($sDefinition);

            $class = $this->dumpValue($sDefinition->getClass());
            if ($nbOccurrences->offsetGet($sDefinition) > 1 || $sDefinition->getMethodCalls() || $sDefinition->getProperties() || null !== $sDefinition->getConfigurator() || false !== strpos($class, '$')) {
                $name = $this->getNextVariableName();
                $variableMap->offsetSet($sDefinition, new Variable($name));

                // a construct like:
                // $a = new ServiceA(ServiceB $b); $b = new ServiceB(ServiceA $a);
                // this is an indication for a wrong implementation, you can circumvent this problem
                // by setting up your service structure like this:
                // $b = new ServiceB();
                // $a = new ServiceA(ServiceB $b);
                // $b->setServiceA(ServiceA $a);
                if ($this->hasReference($id, $sDefinition->getArguments())) {
                    throw new ServiceCircularReferenceException($id, array($id));
                }

                $code .= $this->addNewInstance($id, $sDefinition, '$'.$name, ' = ');

                if (!$this->hasReference($id, $sDefinition->getMethodCalls(), true) && !$this->hasReference($id, $sDefinition->getProperties(), true)) {
                    $code .= $this->addServiceProperties($sDefinition, $name);
                    $code .= $this->addServiceMethodCalls($sDefinition, $name);
                    $code .= $this->addServiceConfigurator($sDefinition, $name);
                }

                $code .= "\n";
            }
        }

        return $code;
    }

    /**
     * Adds the service return statement.
     *
     * @param string     $id         Service id
     * @param Definition $definition
     *
     * @return string
     */
    private function addServiceReturn($id, Definition $definition)
    {
        if ($this->isSimpleInstance($id, $definition)) {
            return "    }\n";
        }

        return "\n        return \$instance;\n    }\n";
    }

    /**
     * Generates the service instance.
     *
     * @param string     $id
     * @param Definition $definition
     *
     * @return string
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function addServiceInstance($id, Definition $definition)
    {
        $class = $this->dumpValue($definition->getClass());

        if (0 === strpos($class, "'") && !preg_match('/^\'(?:\\\{2})?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\\\{2}[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*\'$/', $class)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid class name for the "%s" service.', $class, $id));
        }

        $simple = $this->isSimpleInstance($id, $definition);
        $isProxyCandidate = $this->getProxyDumper()->isProxyCandidate($definition);
        $instantiation = '';

        if (!$isProxyCandidate && $definition->isShared() && ContainerInterface::SCOPE_CONTAINER === $definition->getScope(false)) {
            $instantiation = "\$this->services['$id'] = ".($simple ? '' : '$instance');
        } elseif (!$isProxyCandidate && $definition->isShared() && ContainerInterface::SCOPE_PROTOTYPE !== $scope = $definition->getScope(false)) {
            $instantiation = "\$this->services['$id'] = \$this->scopedServices['$scope']['$id'] = ".($simple ? '' : '$instance');
        } elseif (!$simple) {
            $instantiation = '$instance';
        }

        $return = '';
        if ($simple) {
            $return = 'return ';
        } else {
            $instantiation .= ' = ';
        }

        $code = $this->addNewInstance($id, $definition, $return, $instantiation);

        if (!$simple) {
            $code .= "\n";
        }

        return $code;
    }

    /**
     * Checks if the definition is a simple instance.
     *
     * @param string     $id
     * @param Definition $definition
     *
     * @return bool
     */
    private function isSimpleInstance($id, Definition $definition)
    {
        foreach (array_merge(array($definition), $this->getInlinedDefinitions($definition)) as $sDefinition) {
            if ($definition !== $sDefinition && !$this->hasReference($id, $sDefinition->getMethodCalls())) {
                continue;
            }

            if ($sDefinition->getMethodCalls() || $sDefinition->getProperties() || $sDefinition->getConfigurator()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds method calls to a service definition.
     *
     * @param Definition $definition
     * @param string     $variableName
     *
     * @return string
     */
    private function addServiceMethodCalls(Definition $definition, $variableName = 'instance')
    {
        $calls = '';
        foreach ($definition->getMethodCalls() as $call) {
            $arguments = array();
            foreach ($call[1] as $value) {
                $arguments[] = $this->dumpValue($value);
            }

            $calls .= $this->wrapServiceConditionals($call[1], sprintf("        \$%s->%s(%s);\n", $variableName, $call[0], implode(', ', $arguments)));
        }

        return $calls;
    }

    private function addServiceProperties(Definition $definition, $variableName = 'instance')
    {
        $code = '';
        foreach ($definition->getProperties() as $name => $value) {
            $code .= sprintf("        \$%s->%s = %s;\n", $variableName, $name, $this->dumpValue($value));
        }

        return $code;
    }

    /**
     * Generates the inline definition setup.
     *
     * @param string     $id
     * @param Definition $definition
     *
     * @return string
     *
     * @throws ServiceCircularReferenceException when the container contains a circular reference
     */
    private function addServiceInlinedDefinitionsSetup($id, Definition $definition)
    {
        $this->referenceVariables[$id] = new Variable('instance');

        $code = '';
        $processed = new \SplObjectStorage();
        foreach ($this->getInlinedDefinitions($definition) as $iDefinition) {
            if ($processed->contains($iDefinition)) {
                continue;
            }
            $processed->offsetSet($iDefinition);

            if (!$this->hasReference($id, $iDefinition->getMethodCalls(), true) && !$this->hasReference($id, $iDefinition->getProperties(), true)) {
                continue;
            }

            // if the instance is simple, the return statement has already been generated
            // so, the only possible way to get there is because of a circular reference
            if ($this->isSimpleInstance($id, $definition)) {
                throw new ServiceCircularReferenceException($id, array($id));
            }

            $name = (string) $this->definitionVariables->offsetGet($iDefinition);
            $code .= $this->addServiceProperties($iDefinition, $name);
            $code .= $this->addServiceMethodCalls($iDefinition, $name);
            $code .= $this->addServiceConfigurator($iDefinition, $name);
        }

        if ('' !== $code) {
            $code .= "\n";
        }

        return $code;
    }

    /**
     * Adds configurator definition.
     *
     * @param Definition $definition
     * @param string     $variableName
     *
     * @return string
     */
    private function addServiceConfigurator(Definition $definition, $variableName = 'instance')
    {
        if (!$callable = $definition->getConfigurator()) {
            return '';
        }

        if (is_array($callable)) {
            if ($callable[0] instanceof Reference
                || ($callable[0] instanceof Definition && $this->definitionVariables->contains($callable[0]))) {
                return sprintf("        %s->%s(\$%s);\n", $this->dumpValue($callable[0]), $callable[1], $variableName);
            }

            $class = $this->dumpValue($callable[0]);
            // If the class is a string we can optimize call_user_func away
            if (0 === strpos($class, "'")) {
                return sprintf("        %s::%s(\$%s);\n", $this->dumpLiteralClass($class), $callable[1], $variableName);
            }

            return sprintf("        call_user_func(array(%s, '%s'), \$%s);\n", $this->dumpValue($callable[0]), $callable[1], $variableName);
        }

        return sprintf("        %s(\$%s);\n", $callable, $variableName);
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
        $this->definitionVariables = new \SplObjectStorage();
        $this->referenceVariables = array();
        $this->variableCount = 0;

        $return = array();

        if ($definition->isSynthetic()) {
            $return[] = '@throws RuntimeException always since this service is expected to be injected dynamically';
        } elseif ($class = $definition->getClass()) {
            $return[] = sprintf(0 === strpos($class, '%') ? '@return object A %1$s instance' : '@return \%s', ltrim($class, '\\'));
        } elseif ($definition->getFactory()) {
            $factory = $definition->getFactory();
            if (is_string($factory)) {
                $return[] = sprintf('@return object An instance returned by %s()', $factory);
            } elseif (is_array($factory) && (is_string($factory[0]) || $factory[0] instanceof Definition || $factory[0] instanceof Reference)) {
                if (is_string($factory[0]) || $factory[0] instanceof Reference) {
                    $return[] = sprintf('@return object An instance returned by %s::%s()', (string) $factory[0], $factory[1]);
                } elseif ($factory[0] instanceof Definition) {
                    $return[] = sprintf('@return object An instance returned by %s::%s()', $factory[0]->getClass(), $factory[1]);
                }
            }
        } elseif ($definition->getFactoryClass(false)) {
            $return[] = sprintf('@return object An instance returned by %s::%s()', $definition->getFactoryClass(false), $definition->getFactoryMethod(false));
        } elseif ($definition->getFactoryService(false)) {
            $return[] = sprintf('@return object An instance returned by %s::%s()', $definition->getFactoryService(false), $definition->getFactoryMethod(false));
        }

        $scope = $definition->getScope(false);
        if (!in_array($scope, array(ContainerInterface::SCOPE_CONTAINER, ContainerInterface::SCOPE_PROTOTYPE))) {
            if ($return && 0 === strpos($return[count($return) - 1], '@return')) {
                $return[] = '';
            }
            $return[] = sprintf("@throws InactiveScopeException when the '%s' service is requested while the '%s' scope is not active", $id, $scope);
        }

        if ($definition->isDeprecated()) {
            if ($return && 0 === strpos($return[count($return) - 1], '@return')) {
                $return[] = '';
            }

            $return[] = sprintf('@deprecated %s', $definition->getDeprecationMessage($id));
        }

        $return = str_replace("\n     * \n", "\n     *\n", implode("\n     * ", $return));

        $shared = $definition->isShared() && ContainerInterface::SCOPE_PROTOTYPE !== $scope ? ' shared' : '';
        $public = $definition->isPublic() ? 'public' : 'private';
        $autowired = $definition->isAutowired() ? ' autowired' : '';

        if ($definition->isLazy()) {
            $lazyInitialization = '$lazyLoad = true';
        } else {
            $lazyInitialization = '';
        }

        // with proxies, for 5.3.3 compatibility, the getter must be public to be accessible to the initializer
        $isProxyCandidate = $this->getProxyDumper()->isProxyCandidate($definition);
        $visibility = $isProxyCandidate ? 'public' : 'protected';
        $code = <<<EOF

    /*{$this->docStar}
     * Gets the $public '$id'$shared$autowired service.
     *
     * $return
     */
    {$visibility} function get{$this->camelize($id)}Service($lazyInitialization)
    {

EOF;

        $code .= $isProxyCandidate ? $this->getProxyDumper()->getProxyFactoryCode($definition, $id) : '';

        if (!in_array($scope, array(ContainerInterface::SCOPE_CONTAINER, ContainerInterface::SCOPE_PROTOTYPE))) {
            $code .= <<<EOF
        if (!isset(\$this->scopedServices['$scope'])) {
            throw new InactiveScopeException('$id', '$scope');
        }


EOF;
        }

        if ($definition->isSynthetic()) {
            $code .= sprintf("        throw new RuntimeException('You have requested a synthetic service (\"%s\"). The DIC does not know how to construct this service.');\n    }\n", $id);
        } else {
            if ($definition->isDeprecated()) {
                $code .= sprintf("        @trigger_error(%s, E_USER_DEPRECATED);\n\n", var_export($definition->getDeprecationMessage($id), true));
            }

            $code .=
                $this->addServiceInclude($definition).
                $this->addServiceLocalTempVariables($id, $definition).
                $this->addServiceInlinedDefinitions($id, $definition).
                $this->addServiceInstance($id, $definition).
                $this->addServiceInlinedDefinitionsSetup($id, $definition).
                $this->addServiceProperties($definition).
                $this->addServiceMethodCalls($definition).
                $this->addServiceConfigurator($definition).
                $this->addServiceReturn($id, $definition)
            ;
        }

        $this->definitionVariables = null;
        $this->referenceVariables = null;

        return $code;
    }

    /**
     * Adds multiple services.
     *
     * @return string
     */
    private function addServices()
    {
        $publicServices = $privateServices = $synchronizers = '';
        $definitions = $this->container->getDefinitions();
        ksort($definitions);
        foreach ($definitions as $id => $definition) {
            if ($definition->isPublic()) {
                $publicServices .= $this->addService($id, $definition);
            } else {
                $privateServices .= $this->addService($id, $definition);
            }

            $synchronizers .= $this->addServiceSynchronizer($id, $definition);
        }

        return $publicServices.$synchronizers.$privateServices;
    }

    /**
     * Adds synchronizer methods.
     *
     * @param string     $id         A service identifier
     * @param Definition $definition A Definition instance
     *
     * @return string|null
     *
     * @deprecated since version 2.7, will be removed in 3.0.
     */
    private function addServiceSynchronizer($id, Definition $definition)
    {
        if (!$definition->isSynchronized(false)) {
            return;
        }

        if ('request' !== $id) {
            @trigger_error('Synchronized services were deprecated in version 2.7 and won\'t work anymore in 3.0.', E_USER_DEPRECATED);
        }

        $code = '';
        foreach ($this->container->getDefinitions() as $definitionId => $definition) {
            foreach ($definition->getMethodCalls() as $call) {
                foreach ($call[1] as $argument) {
                    if ($argument instanceof Reference && $id == (string) $argument) {
                        $arguments = array();
                        foreach ($call[1] as $value) {
                            $arguments[] = $this->dumpValue($value);
                        }

                        $call = $this->wrapServiceConditionals($call[1], sprintf("\$this->get('%s')->%s(%s);", $definitionId, $call[0], implode(', ', $arguments)));

                        $code .= <<<EOF
        if (\$this->initialized('$definitionId')) {
            $call
        }

EOF;
                    }
                }
            }
        }

        if (!$code) {
            return;
        }

        return <<<EOF

    /*{$this->docStar}
     * Updates the '$id' service.
     */
    protected function synchronize{$this->camelize($id)}Service()
    {
$code    }

EOF;
    }

    private function addNewInstance($id, Definition $definition, $return, $instantiation)
    {
        $class = $this->dumpValue($definition->getClass());

        $arguments = array();
        foreach ($definition->getArguments() as $value) {
            $arguments[] = $this->dumpValue($value);
        }

        if (null !== $definition->getFactory()) {
            $callable = $definition->getFactory();
            if (is_array($callable)) {
                if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $callable[1])) {
                    throw new RuntimeException(sprintf('Cannot dump definition because of invalid factory method (%s)', $callable[1] ?: 'n/a'));
                }

                if ($callable[0] instanceof Reference
                    || ($callable[0] instanceof Definition && $this->definitionVariables->contains($callable[0]))) {
                    return sprintf("        $return{$instantiation}%s->%s(%s);\n", $this->dumpValue($callable[0]), $callable[1], $arguments ? implode(', ', $arguments) : '');
                }

                $class = $this->dumpValue($callable[0]);
                // If the class is a string we can optimize call_user_func away
                if (0 === strpos($class, "'")) {
                    return sprintf("        $return{$instantiation}%s::%s(%s);\n", $this->dumpLiteralClass($class), $callable[1], $arguments ? implode(', ', $arguments) : '');
                }

                return sprintf("        $return{$instantiation}call_user_func(array(%s, '%s')%s);\n", $this->dumpValue($callable[0]), $callable[1], $arguments ? ', '.implode(', ', $arguments) : '');
            }

            return sprintf("        $return{$instantiation}\\%s(%s);\n", $callable, $arguments ? implode(', ', $arguments) : '');
        } elseif (null !== $definition->getFactoryMethod(false)) {
            if (null !== $definition->getFactoryClass(false)) {
                $class = $this->dumpValue($definition->getFactoryClass(false));

                // If the class is a string we can optimize call_user_func away
                if (0 === strpos($class, "'")) {
                    return sprintf("        $return{$instantiation}%s::%s(%s);\n", $this->dumpLiteralClass($class), $definition->getFactoryMethod(false), $arguments ? implode(', ', $arguments) : '');
                }

                return sprintf("        $return{$instantiation}call_user_func(array(%s, '%s')%s);\n", $this->dumpValue($definition->getFactoryClass(false)), $definition->getFactoryMethod(false), $arguments ? ', '.implode(', ', $arguments) : '');
            }

            if (null !== $definition->getFactoryService(false)) {
                return sprintf("        $return{$instantiation}%s->%s(%s);\n", $this->getServiceCall($definition->getFactoryService(false)), $definition->getFactoryMethod(false), implode(', ', $arguments));
            }

            throw new RuntimeException(sprintf('Factory method requires a factory service or factory class in service definition for %s', $id));
        }

        if (false !== strpos($class, '$')) {
            return sprintf("        \$class = %s;\n\n        $return{$instantiation}new \$class(%s);\n", $class, implode(', ', $arguments));
        }

        return sprintf("        $return{$instantiation}new %s(%s);\n", $this->dumpLiteralClass($class), implode(', ', $arguments));
    }

    /**
     * Adds the class headers.
     *
     * @param string $class     Class name
     * @param string $baseClass The name of the base class
     * @param string $namespace The class namespace
     *
     * @return string
     */
    private function startClass($class, $baseClass, $namespace)
    {
        $bagClass = $this->container->isFrozen() ? 'use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;' : 'use Symfony\Component\DependencyInjection\ParameterBag\\ParameterBag;';
        $namespaceLine = $namespace ? "\nnamespace $namespace;\n" : '';

        return <<<EOF
<?php
$namespaceLine
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
$bagClass

/*{$this->docStar}
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class $class extends $baseClass
{
    private \$parameters;
    private \$targetDirs = array();

EOF;
    }

    /**
     * Adds the constructor.
     *
     * @return string
     */
    private function addConstructor()
    {
        $targetDirs = $this->exportTargetDirs();
        $arguments = $this->container->getParameterBag()->all() ? 'new ParameterBag($this->getDefaultParameters())' : null;

        $code = <<<EOF

    public function __construct()
    {{$targetDirs}
        parent::__construct($arguments);

EOF;

        if (count($scopes = $this->container->getScopes(false)) > 0) {
            $code .= "\n";
            $code .= '        $this->scopes = '.$this->dumpValue($scopes).";\n";
            $code .= '        $this->scopeChildren = '.$this->dumpValue($this->container->getScopeChildren(false)).";\n";
        }

        $code .= $this->addMethodMap();
        $code .= $this->addAliases();

        $code .= <<<'EOF'
    }

EOF;

        return $code;
    }

    /**
     * Adds the constructor for a frozen container.
     *
     * @return string
     */
    private function addFrozenConstructor()
    {
        $targetDirs = $this->exportTargetDirs();

        $code = <<<EOF

    public function __construct()
    {{$targetDirs}
EOF;

        if ($this->container->getParameterBag()->all()) {
            $code .= "\n        \$this->parameters = \$this->getDefaultParameters();\n";
        }

        $code .= <<<'EOF'

        $this->services =
        $this->scopedServices =
        $this->scopeStacks = array();
EOF;

        $code .= "\n";
        if (count($scopes = $this->container->getScopes(false)) > 0) {
            $code .= '        $this->scopes = '.$this->dumpValue($scopes).";\n";
            $code .= '        $this->scopeChildren = '.$this->dumpValue($this->container->getScopeChildren(false)).";\n";
        } else {
            $code .= "        \$this->scopes = array();\n";
            $code .= "        \$this->scopeChildren = array();\n";
        }

        $code .= $this->addMethodMap();
        $code .= $this->addAliases();

        $code .= <<<'EOF'
    }

EOF;

        return $code;
    }

    /**
     * Adds the constructor for a frozen container.
     *
     * @return string
     */
    private function addFrozenCompile()
    {
        return <<<EOF

    /*{$this->docStar}
     * {@inheritdoc}
     */
    public function compile()
    {
        throw new LogicException('You cannot compile a dumped frozen container.');
    }

EOF;
    }

    /**
     * Adds the isFrozen method for a frozen container.
     *
     * @return string
     */
    private function addIsFrozenMethod()
    {
        return <<<EOF

    /*{$this->docStar}
     * {@inheritdoc}
     */
    public function isFrozen()
    {
        return true;
    }

EOF;
    }

    /**
     * Adds the methodMap property definition.
     *
     * @return string
     */
    private function addMethodMap()
    {
        if (!$definitions = $this->container->getDefinitions()) {
            return '';
        }

        $code = "        \$this->methodMap = array(\n";
        ksort($definitions);
        foreach ($definitions as $id => $definition) {
            $code .= '            '.var_export($id, true).' => '.var_export('get'.$this->camelize($id).'Service', true).",\n";
        }

        return $code."        );\n";
    }

    /**
     * Adds the aliases property definition.
     *
     * @return string
     */
    private function addAliases()
    {
        if (!$aliases = $this->container->getAliases()) {
            return $this->container->isFrozen() ? "\n        \$this->aliases = array();\n" : '';
        }

        $code = "        \$this->aliases = array(\n";
        ksort($aliases);
        foreach ($aliases as $alias => $id) {
            $id = (string) $id;
            while (isset($aliases[$id])) {
                $id = (string) $aliases[$id];
            }
            $code .= '            '.var_export($alias, true).' => '.var_export($id, true).",\n";
        }

        return $code."        );\n";
    }

    /**
     * Adds default parameters method.
     *
     * @return string
     */
    private function addDefaultParametersMethod()
    {
        if (!$this->container->getParameterBag()->all()) {
            return '';
        }

        $parameters = $this->exportParameters($this->container->getParameterBag()->all());

        $code = '';
        if ($this->container->isFrozen()) {
            $code .= <<<'EOF'

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        $name = strtolower($name);

        if (!(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters))) {
            throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($name)
    {
        $name = strtolower($name);

        return isset($this->parameters[$name]) || array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $this->parameterBag = new FrozenParameterBag($this->parameters);
        }

        return $this->parameterBag;
    }

EOF;
            if ('' === $this->docStar) {
                $code = str_replace('/**', '/*', $code);
            }
        }

        $code .= <<<EOF

    /*{$this->docStar}
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return $parameters;
    }

EOF;

        return $code;
    }

    /**
     * Exports parameters.
     *
     * @param array  $parameters
     * @param string $path
     * @param int    $indent
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function exportParameters(array $parameters, $path = '', $indent = 12)
    {
        $php = array();
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $value = $this->exportParameters($value, $path.'/'.$key, $indent + 4);
            } elseif ($value instanceof Variable) {
                throw new InvalidArgumentException(sprintf('You cannot dump a container with parameters that contain variable references. Variable "%s" found in "%s".', $value, $path.'/'.$key));
            } elseif ($value instanceof Definition) {
                throw new InvalidArgumentException(sprintf('You cannot dump a container with parameters that contain service definitions. Definition for "%s" found in "%s".', $value->getClass(), $path.'/'.$key));
            } elseif ($value instanceof Reference) {
                throw new InvalidArgumentException(sprintf('You cannot dump a container with parameters that contain references to other services (reference to service "%s" found in "%s").', $value, $path.'/'.$key));
            } elseif ($value instanceof Expression) {
                throw new InvalidArgumentException(sprintf('You cannot dump a container with parameters that contain expressions. Expression "%s" found in "%s".', $value, $path.'/'.$key));
            } else {
                $value = $this->export($value);
            }

            $php[] = sprintf('%s%s => %s,', str_repeat(' ', $indent), var_export($key, true), $value);
        }

        return sprintf("array(\n%s\n%s)", implode("\n", $php), str_repeat(' ', $indent - 4));
    }

    /**
     * Ends the class definition.
     *
     * @return string
     */
    private function endClass()
    {
        return <<<'EOF'
}

EOF;
    }

    /**
     * Wraps the service conditionals.
     *
     * @param string $value
     * @param string $code
     *
     * @return string
     */
    private function wrapServiceConditionals($value, $code)
    {
        if (!$services = ContainerBuilder::getServiceConditionals($value)) {
            return $code;
        }

        $conditions = array();
        foreach ($services as $service) {
            $conditions[] = sprintf("\$this->has('%s')", $service);
        }

        // re-indent the wrapped code
        $code = implode("\n", array_map(function ($line) { return $line ? '    '.$line : $line; }, explode("\n", $code)));

        return sprintf("        if (%s) {\n%s        }\n", implode(' && ', $conditions), $code);
    }

    /**
     * Builds service calls from arguments.
     */
    private function getServiceCallsFromArguments(array $arguments, array &$calls, array &$behavior)
    {
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                $this->getServiceCallsFromArguments($argument, $calls, $behavior);
            } elseif ($argument instanceof Reference) {
                $id = (string) $argument;

                if (!isset($calls[$id])) {
                    $calls[$id] = 0;
                }
                if (!isset($behavior[$id])) {
                    $behavior[$id] = $argument->getInvalidBehavior();
                } elseif (ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE !== $behavior[$id]) {
                    $behavior[$id] = $argument->getInvalidBehavior();
                }

                ++$calls[$id];
            }
        }
    }

    /**
     * Returns the inline definition.
     *
     * @return array
     */
    private function getInlinedDefinitions(Definition $definition)
    {
        if (false === $this->inlinedDefinitions->contains($definition)) {
            $definitions = array_merge(
                $this->getDefinitionsFromArguments($definition->getArguments()),
                $this->getDefinitionsFromArguments($definition->getMethodCalls()),
                $this->getDefinitionsFromArguments($definition->getProperties()),
                $this->getDefinitionsFromArguments(array($definition->getConfigurator())),
                $this->getDefinitionsFromArguments(array($definition->getFactory()))
            );

            $this->inlinedDefinitions->offsetSet($definition, $definitions);

            return $definitions;
        }

        return $this->inlinedDefinitions->offsetGet($definition);
    }

    /**
     * Gets the definition from arguments.
     *
     * @return array
     */
    private function getDefinitionsFromArguments(array $arguments)
    {
        $definitions = array();
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                $definitions = array_merge($definitions, $this->getDefinitionsFromArguments($argument));
            } elseif ($argument instanceof Definition) {
                $definitions = array_merge(
                    $definitions,
                    $this->getInlinedDefinitions($argument),
                    array($argument)
                );
            }
        }

        return $definitions;
    }

    /**
     * Checks if a service id has a reference.
     *
     * @param string $id
     * @param array  $arguments
     * @param bool   $deep
     * @param array  $visited
     *
     * @return bool
     */
    private function hasReference($id, array $arguments, $deep = false, array &$visited = array())
    {
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                if ($this->hasReference($id, $argument, $deep, $visited)) {
                    return true;
                }
            } elseif ($argument instanceof Reference) {
                $argumentId = (string) $argument;
                if ($id === $argumentId) {
                    return true;
                }

                if ($deep && !isset($visited[$argumentId]) && 'service_container' !== $argumentId) {
                    $visited[$argumentId] = true;

                    $service = $this->container->getDefinition($argumentId);

                    // if the proxy manager is enabled, disable searching for references in lazy services,
                    // as these services will be instantiated lazily and don't have direct related references.
                    if ($service->isLazy() && !$this->getProxyDumper() instanceof NullDumper) {
                        continue;
                    }

                    $arguments = array_merge($service->getMethodCalls(), $service->getArguments(), $service->getProperties());

                    if ($this->hasReference($id, $arguments, $deep, $visited)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Dumps values.
     *
     * @param mixed $value
     * @param bool  $interpolate
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function dumpValue($value, $interpolate = true)
    {
        if (is_array($value)) {
            $code = array();
            foreach ($value as $k => $v) {
                $code[] = sprintf('%s => %s', $this->dumpValue($k, $interpolate), $this->dumpValue($v, $interpolate));
            }

            return sprintf('array(%s)', implode(', ', $code));
        } elseif ($value instanceof Definition) {
            if (null !== $this->definitionVariables && $this->definitionVariables->contains($value)) {
                return $this->dumpValue($this->definitionVariables->offsetGet($value), $interpolate);
            }
            if ($value->getMethodCalls()) {
                throw new RuntimeException('Cannot dump definitions which have method calls.');
            }
            if ($value->getProperties()) {
                throw new RuntimeException('Cannot dump definitions which have properties.');
            }
            if (null !== $value->getConfigurator()) {
                throw new RuntimeException('Cannot dump definitions which have a configurator.');
            }

            $arguments = array();
            foreach ($value->getArguments() as $argument) {
                $arguments[] = $this->dumpValue($argument);
            }

            if (null !== $value->getFactory()) {
                $factory = $value->getFactory();

                if (is_string($factory)) {
                    return sprintf('\\%s(%s)', $factory, implode(', ', $arguments));
                }

                if (is_array($factory)) {
                    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $factory[1])) {
                        throw new RuntimeException(sprintf('Cannot dump definition because of invalid factory method (%s)', $factory[1] ?: 'n/a'));
                    }

                    if (is_string($factory[0])) {
                        return sprintf('%s::%s(%s)', $this->dumpLiteralClass($this->dumpValue($factory[0])), $factory[1], implode(', ', $arguments));
                    }

                    if ($factory[0] instanceof Definition) {
                        return sprintf("call_user_func(array(%s, '%s')%s)", $this->dumpValue($factory[0]), $factory[1], count($arguments) > 0 ? ', '.implode(', ', $arguments) : '');
                    }

                    if ($factory[0] instanceof Reference) {
                        return sprintf('%s->%s(%s)', $this->dumpValue($factory[0]), $factory[1], implode(', ', $arguments));
                    }
                }

                throw new RuntimeException('Cannot dump definition because of invalid factory');
            }

            if (null !== $value->getFactoryMethod(false)) {
                if (null !== $value->getFactoryClass(false)) {
                    return sprintf("call_user_func(array(%s, '%s')%s)", $this->dumpValue($value->getFactoryClass(false)), $value->getFactoryMethod(false), count($arguments) > 0 ? ', '.implode(', ', $arguments) : '');
                } elseif (null !== $value->getFactoryService(false)) {
                    $service = $this->dumpValue($value->getFactoryService(false));

                    return sprintf('%s->%s(%s)', 0 === strpos($service, '$') ? sprintf('$this->get(%s)', $service) : $this->getServiceCall($value->getFactoryService(false)), $value->getFactoryMethod(false), implode(', ', $arguments));
                }

                throw new RuntimeException('Cannot dump definitions which have factory method without factory service or factory class.');
            }

            $class = $value->getClass();
            if (null === $class) {
                throw new RuntimeException('Cannot dump definitions which have no class nor factory.');
            }

            return sprintf('new %s(%s)', $this->dumpLiteralClass($this->dumpValue($class)), implode(', ', $arguments));
        } elseif ($value instanceof Variable) {
            return '$'.$value;
        } elseif ($value instanceof Reference) {
            if (null !== $this->referenceVariables && isset($this->referenceVariables[$id = (string) $value])) {
                return $this->dumpValue($this->referenceVariables[$id], $interpolate);
            }

            return $this->getServiceCall((string) $value, $value);
        } elseif ($value instanceof Expression) {
            return $this->getExpressionLanguage()->compile((string) $value, array('this' => 'container'));
        } elseif ($value instanceof Parameter) {
            return $this->dumpParameter($value);
        } elseif (true === $interpolate && is_string($value)) {
            if (preg_match('/^%([^%]+)%$/', $value, $match)) {
                // we do this to deal with non string values (Boolean, integer, ...)
                // the preg_replace_callback converts them to strings
                return $this->dumpParameter(strtolower($match[1]));
            } else {
                $that = $this;
                $replaceParameters = function ($match) use ($that) {
                    return "'.".$that->dumpParameter(strtolower($match[2])).".'";
                };

                $code = str_replace('%%', '%', preg_replace_callback('/(?<!%)(%)([^%]+)\1/', $replaceParameters, $this->export($value)));

                return $code;
            }
        } elseif (is_object($value) || is_resource($value)) {
            throw new RuntimeException('Unable to dump a service container if a parameter is an object or a resource.');
        }

        return $this->export($value);
    }

    /**
     * Dumps a string to a literal (aka PHP Code) class value.
     *
     * @param string $class
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function dumpLiteralClass($class)
    {
        if (false !== strpos($class, '$')) {
            throw new RuntimeException('Cannot dump definitions which have a variable class name.');
        }
        if (0 !== strpos($class, "'") || !preg_match('/^\'(?:\\\{2})?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\\\{2}[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*\'$/', $class)) {
            throw new RuntimeException(sprintf('Cannot dump definition because of invalid class name (%s)', $class ?: 'n/a'));
        }

        $class = substr(str_replace('\\\\', '\\', $class), 1, -1);

        return 0 === strpos($class, '\\') ? $class : '\\'.$class;
    }

    /**
     * Dumps a parameter.
     *
     * @param string $name
     *
     * @return string
     */
    public function dumpParameter($name)
    {
        if ($this->container->isFrozen() && $this->container->hasParameter($name)) {
            return $this->dumpValue($this->container->getParameter($name), false);
        }

        return sprintf("\$this->getParameter('%s')", strtolower($name));
    }

    /**
     * @deprecated since version 2.6.2, to be removed in 3.0.
     *             Use \Symfony\Component\DependencyInjection\ContainerBuilder::addExpressionLanguageProvider instead.
     *
     * @param ExpressionFunctionProviderInterface $provider
     */
    public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 2.6.2 and will be removed in 3.0. Use the Symfony\Component\DependencyInjection\ContainerBuilder::addExpressionLanguageProvider method instead.', E_USER_DEPRECATED);

        $this->expressionLanguageProviders[] = $provider;
    }

    /**
     * Gets a service call.
     *
     * @param string    $id
     * @param Reference $reference
     *
     * @return string
     */
    private function getServiceCall($id, Reference $reference = null)
    {
        while ($this->container->hasAlias($id)) {
            $id = (string) $this->container->getAlias($id);
        }

        if ('service_container' === $id) {
            return '$this';
        }

        if (null !== $reference && ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE !== $reference->getInvalidBehavior()) {
            return sprintf('$this->get(\'%s\', ContainerInterface::NULL_ON_INVALID_REFERENCE)', $id);
        }

        return sprintf('$this->get(\'%s\')', $id);
    }

    /**
     * Convert a service id to a valid PHP method name.
     *
     * @param string $id
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function camelize($id)
    {
        $name = Container::camelize($id);

        if (!preg_match('/^[a-zA-Z0-9_\x7f-\xff]+$/', $name)) {
            throw new InvalidArgumentException(sprintf('Service id "%s" cannot be converted to a valid PHP method name.', $id));
        }

        return $name;
    }

    /**
     * Returns the next name to use.
     *
     * @return string
     */
    private function getNextVariableName()
    {
        $firstChars = self::FIRST_CHARS;
        $firstCharsLength = strlen($firstChars);
        $nonFirstChars = self::NON_FIRST_CHARS;
        $nonFirstCharsLength = strlen($nonFirstChars);

        while (true) {
            $name = '';
            $i = $this->variableCount;

            if ('' === $name) {
                $name .= $firstChars[$i % $firstCharsLength];
                $i = (int) ($i / $firstCharsLength);
            }

            while ($i > 0) {
                --$i;
                $name .= $nonFirstChars[$i % $nonFirstCharsLength];
                $i = (int) ($i / $nonFirstCharsLength);
            }

            ++$this->variableCount;

            // check that the name is not reserved
            if (in_array($name, $this->reservedVariables, true)) {
                continue;
            }

            return $name;
        }
    }

    private function getExpressionLanguage()
    {
        if (null === $this->expressionLanguage) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                throw new RuntimeException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
            }
            $providers = array_merge($this->container->getExpressionLanguageProviders(), $this->expressionLanguageProviders);
            $this->expressionLanguage = new ExpressionLanguage(null, $providers);

            if ($this->container->isTrackingResources()) {
                foreach ($providers as $provider) {
                    $this->container->addObjectResource($provider);
                }
            }
        }

        return $this->expressionLanguage;
    }

    private function exportTargetDirs()
    {
        return null === $this->targetDirRegex ? '' : <<<EOF

        \$dir = __DIR__;
        for (\$i = 1; \$i <= {$this->targetDirMaxMatches}; ++\$i) {
            \$this->targetDirs[\$i] = \$dir = dirname(\$dir);
        }
EOF;
    }

    private function export($value)
    {
        if (null !== $this->targetDirRegex && is_string($value) && preg_match($this->targetDirRegex, $value, $matches, PREG_OFFSET_CAPTURE)) {
            $prefix = $matches[0][1] ? var_export(substr($value, 0, $matches[0][1]), true).'.' : '';
            $suffix = $matches[0][1] + strlen($matches[0][0]);
            $suffix = isset($value[$suffix]) ? '.'.var_export(substr($value, $suffix), true) : '';
            $dirname = '__DIR__';

            if (0 < $offset = 1 + $this->targetDirMaxMatches - count($matches)) {
                $dirname = sprintf('$this->targetDirs[%d]', $offset);
            }

            if ($prefix || $suffix) {
                return sprintf('(%s%s%s)', $prefix, $dirname, $suffix);
            }

            return $dirname;
        }

        if (is_string($value) && false !== strpos($value, "\n")) {
            $cleanParts = explode("\n", $value);
            $cleanParts = array_map(function ($part) { return var_export($part, true); }, $cleanParts);

            return implode('."\n".', $cleanParts);
        }

        return var_export($value, true);
    }
}
