<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\Form;

use Symfony\Component\Form\FormRendererEngineInterface;
use Twig\Environment;

// BC/FC with namespaced Twig
class_exists('Twig\Environment');

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface TwigRendererEngineInterface extends FormRendererEngineInterface
{
    public function setEnvironment(Environment $environment);
}
