<?php
namespace PluginInstaller;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class PluginInstaller extends LibraryInstaller {
    
    const INSTALLER_TYPE = 'openemr-plugin';
        
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package) {
        $type = $package->getType();
        
        $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        } else {
            $vendor = '';
            $name = $prettyName;
        }
        
        if ($this->composer->getPackage()) {
            $extra = $this->composer->getPackage()->getExtra();
            
            if ( !empty( $extra['plugin-dir'] ) ) {
                return $extra['plugin-dir']."/$name";
            } else {
                throw new \InvalidArgumentException('You must configure a plugin-dir under extra in composer.json.');
            }
        } else {
            throw new \InvalidArgumentException('The root package is not configured properly.');
        }
        
    }
    
    /**
     * {@inheritDoc}
     */
    public function supports($packageType) {
        return $packageType === self::INSTALLER_TYPE;
    }
    
}