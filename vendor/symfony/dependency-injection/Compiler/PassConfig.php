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

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Compiler Pass Configuration.
 *
 * This class has a default configuration embedded.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PassConfig
{
    const TYPE_AFTER_REMOVING = 'afterRemoving';
    const TYPE_BEFORE_OPTIMIZATION = 'beforeOptimization';
    const TYPE_BEFORE_REMOVING = 'beforeRemoving';
    const TYPE_OPTIMIZE = 'optimization';
    const TYPE_REMOVE = 'removing';

    private $mergePass;
    private $afterRemovingPasses = array();
    private $beforeOptimizationPasses = array();
    private $beforeRemovingPasses = array();
    private $optimizationPasses;
    private $removingPasses;

    public function __construct()
    {
        $this->mergePass = new MergeExtensionConfigurationPass();

        $this->optimizationPasses = array(
            new ExtensionCompilerPass(),
            new ResolveDefinitionTemplatesPass(),
            new DecoratorServicePass(),
            new ResolveParameterPlaceHoldersPass(),
            new CheckDefinitionValidityPass(),
            new ResolveReferencesToAliasesPass(),
            new ResolveInvalidReferencesPass(),
            new AutowirePass(),
            new AnalyzeServiceReferencesPass(true),
            new CheckCircularReferencesPass(),
            new CheckReferenceValidityPass(),
        );

        $this->removingPasses = array(
            new RemovePrivateAliasesPass(),
            new ReplaceAliasByActualDefinitionPass(),
            new RemoveAbstractDefinitionsPass(),
            new RepeatedPass(array(
                new AnalyzeServiceReferencesPass(),
                new InlineServiceDefinitionsPass(),
                new AnalyzeServiceReferencesPass(),
                new RemoveUnusedDefinitionsPass(),
            )),
            new CheckExceptionOnInvalidReferenceBehaviorPass(),
        );
    }

    /**
     * Returns all passes in order to be processed.
     *
     * @return array An array of all passes to process
     */
    public function getPasses()
    {
        return array_merge(
            array($this->mergePass),
            $this->beforeOptimizationPasses,
            $this->optimizationPasses,
            $this->beforeRemovingPasses,
            $this->removingPasses,
            $this->afterRemovingPasses
        );
    }

    /**
     * Adds a pass.
     *
     * @param CompilerPassInterface $pass A Compiler pass
     * @param string                $type The pass type
     *
     * @throws InvalidArgumentException when a pass type doesn't exist
     */
    public function addPass(CompilerPassInterface $pass, $type = self::TYPE_BEFORE_OPTIMIZATION)
    {
        $property = $type.'Passes';
        if (!isset($this->$property)) {
            throw new InvalidArgumentException(sprintf('Invalid type "%s".', $type));
        }

        $this->{$property}[] = $pass;
    }

    /**
     * Gets all passes for the AfterRemoving pass.
     *
     * @return array An array of passes
     */
    public function getAfterRemovingPasses()
    {
        return $this->afterRemovingPasses;
    }

    /**
     * Gets all passes for the BeforeOptimization pass.
     *
     * @return array An array of passes
     */
    public function getBeforeOptimizationPasses()
    {
        return $this->beforeOptimizationPasses;
    }

    /**
     * Gets all passes for the BeforeRemoving pass.
     *
     * @return array An array of passes
     */
    public function getBeforeRemovingPasses()
    {
        return $this->beforeRemovingPasses;
    }

    /**
     * Gets all passes for the Optimization pass.
     *
     * @return array An array of passes
     */
    public function getOptimizationPasses()
    {
        return $this->optimizationPasses;
    }

    /**
     * Gets all passes for the Removing pass.
     *
     * @return array An array of passes
     */
    public function getRemovingPasses()
    {
        return $this->removingPasses;
    }

    /**
     * Gets the Merge pass.
     *
     * @return CompilerPassInterface The merge pass
     */
    public function getMergePass()
    {
        return $this->mergePass;
    }

    public function setMergePass(CompilerPassInterface $pass)
    {
        $this->mergePass = $pass;
    }

    /**
     * Sets the AfterRemoving passes.
     *
     * @param array $passes An array of passes
     */
    public function setAfterRemovingPasses(array $passes)
    {
        $this->afterRemovingPasses = $passes;
    }

    /**
     * Sets the BeforeOptimization passes.
     *
     * @param array $passes An array of passes
     */
    public function setBeforeOptimizationPasses(array $passes)
    {
        $this->beforeOptimizationPasses = $passes;
    }

    /**
     * Sets the BeforeRemoving passes.
     *
     * @param array $passes An array of passes
     */
    public function setBeforeRemovingPasses(array $passes)
    {
        $this->beforeRemovingPasses = $passes;
    }

    /**
     * Sets the Optimization passes.
     *
     * @param array $passes An array of passes
     */
    public function setOptimizationPasses(array $passes)
    {
        $this->optimizationPasses = $passes;
    }

    /**
     * Sets the Removing passes.
     *
     * @param array $passes An array of passes
     */
    public function setRemovingPasses(array $passes)
    {
        $this->removingPasses = $passes;
    }
}
