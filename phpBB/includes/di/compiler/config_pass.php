<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
    exit;
}

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class phpbb_di_compiler_config_pass implements CompilerPassInterface
{
    private $config_file;

    public function __construct($config_file, $phpbb_root_path, $php_ext)
    {
        $this->config_file = $config_file;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
    }

    public function process(ContainerBuilder $container)
    {
        require $this->config_file;

        $container->setParameter('core.root_path', $this->phpbb_root_path);
        $container->setParameter('core.php_ext', $this->php_ext);

        $container->setParameter('core.table_prefix', $table_prefix);
        $container->setParameter('cache.driver.class', $acm_type);
        $container->setParameter('dbal.driver.class', 'dbal_'.$dbms);
        $container->setParameter('dbal.dbhost', $dbhost);
        $container->setParameter('dbal.dbuser', $dbuser);
        $container->setParameter('dbal.dbpasswd', $dbpasswd);
        $container->setParameter('dbal.dbname', $dbname);
        $container->setParameter('dbal.dbport', $dbport);
        $container->setParameter('dbal.new_link', defined('PHPBB_DB_NEW_LINK') && PHPBB_DB_NEW_LINK);

        $container->set('container', $container);
    }
}
