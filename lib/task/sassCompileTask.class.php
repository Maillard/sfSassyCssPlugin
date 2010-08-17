<?php
/*
 * This file is part of the sfSassyCssPlugin.
 * (c) 2010 Victor Berchet <http://github.com/vicb>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sassCompileTask Compilation task
 *
 * @package    sfSassyCssPlugin
 * @subpackage task
 * @author     Victor Berchet
 */
class sassCompileTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('in', sfCommandArgument::OPTIONAL, 'input folder', sfConfig::get('sf_data_dir') . '/sass'),
      new sfCommandArgument('out', sfCommandArgument::OPTIONAL, 'output folder', sfConfig::get('sf_web_dir') . '/css')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('clean', null, sfCommandOption::PARAMETER_NONE, 'Removing all CSS compiled from sass files'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Generate debug info'),
      new sfCommandOption('style', null, sfCommandOption::PARAMETER_OPTIONAL, '[nested|compact|compressed|expanded]', 'compressed'),
      new sfCommandOption('format', null, sfCommandOption::PARAMETER_OPTIONAL, '[scss|sass]', 'scss'),
    ));

    $this->namespace            = 'sass';
    $this->name                 = 'compile';
    $this->briefDescription     = 'Compiles Sass files';
    $this->detailedDescription  = <<<EOF
The [sass:compile|INFO] task compiles the sass files
Call it with:

  [php symfony sass:compile|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $compiler = sfSassCompilerArgs::getInstance($this->dispatcher);

    $params = array();

    // Clean
    if ($options['clean'])
    {
      $this->logSection('Clean', 'remove previously generated css files');
      $compiler->clean($arguments['in'], $arguments['out']);
    }

    $params[] = '--no-cache';
    $params[] = sprintf('--style %s', $options['style']);

    if ($options['format'] == 'scss')
    {
      $params[] = sprintf('--scss');
    }

    if ($options['debug'])
    {
      $params[] = '--debug-info';
      $params[] = '--line-numbers';
      $params[] = '--line-comments';
    }

    $compiler->compile($arguments['in'], $arguments['out'], $params);

    $this->logSection('Command', $compiler->getCommand());

    $stdout = $compiler->getStdOut();
    $stderr = $compiler->getStdErr();

    if (!empty($stdout))
    {
      $this->logBlock("\n" . $stdout, preg_match('/error /i', $stdout) !== 0?'ERROR':'INFO');
    }

    if (!empty($stderr))
    {
      $this->logSection("\n" . $stderr, 'ERROR');
    }

  }
}