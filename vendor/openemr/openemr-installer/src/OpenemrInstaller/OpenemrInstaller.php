<?php
namespace OpenemrInstaller;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class OpenemrInstaller extends LibraryInstaller {
    
    const INSTALLER_TYPE = 'openemr';
        
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
            
            if (!empty($extra['openemr-dir']) && !empty($extra['openemr-package']) && $extra['openemr-package'] === $prettyName) {
                return $extra['openemr-dir'];
            } else {
                throw new \InvalidArgumentException('Sorry only one package can be installed into the configured webroot.');
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