<?php

namespace Drupal\dependencygraph\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Yaml\Yaml;

class DependencyGraph extends ControllerBase {

  /**
   * Returns with the dependency graph.
   *
   * @return array
   */
  public function content() {
    $modules = [];
    $dependencies = [];
    /** @var \Drupal\Core\Extension\Extension $extension */
    foreach (\Drupal::moduleHandler()->getModuleList() as $extension) {
      $module_path = $extension->getPath();
      if (strpos($module_path, 'core/modules/') !== FALSE) {
        $module_name = $extension->getName();
        $modules[] = $module_name;
        $dependencies[$module_name] = $this->parseDependencies($module_name, $extension->getType());
      }
    }
    $build = [
      '#markup' => $this->getDependencyText($dependencies),
    ];
    return $build;
  }

  /**
   * Returns with the dependency text.
   *
   * @param $dependencies
   * @return string
   */
  private function getDependencyText(array $dependencies): string {
    /**
     * @todo Rewrite this section.
     */
    $no_dependency_text = $this->t("has not any dependencies!");
    $dependency_text = '';
    foreach ($dependencies as $module_name => $dependency) {
      if (!empty($dependency)) {
        $dependency_text .= "<p>$module_name dependencies: </p><p><ul>";
        foreach ($dependency as $dependency_elemet) {
          $dependency_text .= "<li>$dependency_elemet</li>";
        }
        $dependency_text .= '</ul></p>';
      }
      else {
        $dependency_text .= "<p>$module_name $no_dependency_text</p>";
      }
    }
    return $dependency_text;
  }

  /**
   * Parses dependencies of a specific module
   *
   * @param $module
   * @param $type
   * @return array
   */
  private function parseDependencies($module, $type): array {
    $module_name = [];
    $filename = drupal_get_path($type, $module) . "/$module.info.yml";
    $info = Yaml::parseFile($filename);
    if ($type === 'profile') {
      return $this->cleanModuleName($info['install']);
    }
    if (isset($info['dependencies'])) {
      return $this->cleanModuleName($info['dependencies']);
    }
    return $module_name;
  }

  /**
   * Cleans module names.
   *
   * @param $modules
   * @return array
   */
  private function cleanModuleName($modules): array {
    $cleaned = [];
    if (is_array($modules)) {
      foreach ($modules as $module) {
        $clean = explode(':', $module)[1] ?? $module;
        $cleaned[] = explode(' ', $clean)[0] ?? $module;
      }
    }
    return $cleaned;
  }
}
