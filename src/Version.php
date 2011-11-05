<?php
/**
 * Version class example implementation
 *
 * This file is part of the Contao rfccc-1 <https://github.com/Discordier/Contao-ER3>
 * This file is licensed under the Creative Commons Attribution-ShareAlike 3.0 Unported License <http://creativecommons.org/licenses/by-sa/3.0/legalcode>
 *
 * @author Tristan Lins <tristan.lins@infinitysoft.de>
*/


/**
 * Class Version
 *
 * A Version representation class.
 */
class Version
{
	/**
	 * The major component.
	 * @var int
	 */
	protected $major = -1;
	
	/**
	 * The minor component
	 * @var int
	 */
	protected $minor = -1;
	
	/**
	 * The revision component
	 * @var int
	 */
	protected $revision = -1;
	
	/**
	 * The extension component
	 * @var string
	 */
	protected $extension = null;

	/**
	 * The extension string part
	 * @var string
	 */
	protected $extensionA = null;

	/**
	 * The extension numeric part
	 * @var int
	 */
	protected $extensionN = 1;
	
	/**
	 * The release component
	 * @var string
	 */
	protected $release = 'stable';
	
	/**
	 * The build component
	 * @var string
	 */
	protected $build = null;


	/**
	 * Create a new version.
	 *
	 * @param mixed A string, another version object or the single components in this order: major, minor[, revision][, extension][, release], build.
	 */
	public function __construct()
	{
		$args = func_get_args();

		if (count($args) == 1)
		{
			// parse a single string
			if (is_string($args[0]))
			{
				// this pattern is build from the rfccc-1 EBNF of a version
				// @see rfccc-1; 3.1 Versioning
				$MAJOR     = '(?<major>\d+)';
				$MINOR     = '(?<minor>\d+)';
				$REVISION  = '(?:\.(?<revision>\d+))?';
				$EXTENSION = '(?:-(?<extension>[a-zA-Z]+\d*))?';
				$RELEASE   = '(?:(?<headrelease>head)\.?(?<head>[a-zA-Z\d]+)|(?:(?<release>alpha|beta|rc|stable)\.?)?(?<build>\d+))';
				if (preg_match('#^' . $MAJOR . '\.' . $MINOR . $REVISION . $EXTENSION . '\.' . $RELEASE . '#', $args[0], $arrMatch))
				{
					$this->setMajor($arrMatch['major']);
					$this->setMinor($arrMatch['minor']);
					if ($arrMatch['revision'])
						$this->setRevision($arrMatch['revision']);
					if ($arrMatch['extension'])
						$this->setExtension($arrMatch['extension']);
					if ($arrMatch['headrelease'])
					{
						$this->setRelease('head');
						$this->setBuild($arrMatch['head']);
					}
					else if ($arrMatch['release'])
					{
						$this->setRelease($arrMatch['release']);
						$this->setBuild($arrMatch['build']);
					}
					else
					{
						$this->setBuild($arrMatch['build']);
					}
				}
				else
				{
					return false;
				}
			}

			// clone from another version
			else if ($args[0] instanceof Version)
			{
				$this->setMajor($args[0]->getMajor());
				$this->setMinor($args[0]->getMinor());
				$this->setRevision($args[0]->getRevision());
				$this->setExtension($args[0]->getExtension());
				$this->setRelease($args[0]->getRelease());
				$this->setBuild($args[0]->getBuild());
			}

			// invalid call
			else
			{
				return false;
			}
		}

		// parse single components as arguments
		else if (count($args) >= 3 && count($args) <= 6)
		{
			// major, minor and build are mandatory
			$this->setMajor(array_shift($args));
			$this->setMinor(array_shift($args));
			$this->setBuild(array_pop($args));

			// add revision if exists
			if (count($args) && is_numeric($args[0]))
			{
				$this->setRevision(array_shift($args));
			}

			// add extension if exists
			if (count($args) && preg_match('#^[a-zA-Z]+\d*$#', $args[0]) && !in_array($args[0], array('head', 'alpha', 'beta', 'rc', 'stable')))
			{
				$this->setExtension(array_shift($args));
			}

			// add release if exists
			if (count($args) && in_array($args[0], array('head', 'alpha', 'beta', 'rc', 'stable')))
			{
				$this->setRelease(array_shift($args));
			}
		}

		// invalid call
		else if (count($args) > 0)
		{
			return false;
		}
	}


	/**
	 * Check if the version is complete, otherwise throw an IncompleteVersionException
	 *
	 * @throws IncompleteVersionException
	 * @return void
	 */
	protected function validate()
	{
		if ($this->major < 0 || $this->minor < 0 || $this->build < 0)
		{
			throw new IncompleteVersionException($this->major, $this->minor, $this->build);
		}
	}


	/**
	 * Check if this version is compatible to another version, otherwise throw an IncompatibleVersionException.
	 *
	 * @throws IncompatibleVersionException
	 * @param Version $otherVersion
	 * @return void
	 */
	protected function compat(Version $otherVersion)
	{
		if (!$this->isCompatible($otherVersion))
			throw new IncompatibleVersionException($this, $otherVersion);
	}


	/**
	 * Compare this version to another version.
	 *
	 * @param Version $otherVersion
	 * @return int Return a value lower than 0 if this version is smaller than the other version.
	 * Return a value greater than 0 if this version is bigger than the other version.
	 * Return 0 if both versions are equal.
	 */
	public function compare(Version $otherVersion)
	{
		$this->compat($otherVersion);

		// compare major number
		if ($this->major != $otherVersion->major)
		{
			return ($this->major - $otherVersion->major) * 10000000000;
		}

		// compare minor number
		else if ($this->minor != $otherVersion->minor)
		{
			return ($this->minor - $otherVersion->minor) * 100000000;
		}

		// compare revision number
		else if ($this->revision != $otherVersion->revision)
		{
			return ($this->revision - $otherVersion->revision) * 1000000;
		}

		// compare extension number
		else if ($this->extensionN != $otherVersion->extensionN)
		{
			return ($this->extensionN - $otherVersion->extensionN) * 10000;
		}

		// compare release number
		else if ($this->release != $otherVersion->release)
		{
			return ($this->getReleaseNumber() - $otherVersion->getReleaseNumber()) * 100;
		}

		// compare build number
		else if ($this->build != $otherVersion->build)
		{
			// .. as numeric
			if (is_numeric($this->build) && is_numeric($otherVersion->build))
			{
				return ($this->build - $otherVersion->build);
			}

			// .. fallback, compare strings
			else
			{
				return strcmp($this->build, $otherVersion->build);
			}
		}

		// both versions are equal
		return 0;
	}


	/**
	 * Check if this version is greater than the other version.
	 *
	 * @param Version $otherVersion
	 * @return bool
	 */
	public function isGreaterThan(Version $otherVersion)
	{
		return $this->compare($otherVersion) > 0;
	}


	/**
	 * Check if this version is less than the other version.
	 * 
	 * @param Version $otherVersion
	 * @return bool
	 */
	public function isLessThan(Version $otherVersion)
	{
		return $this->compare($otherVersion) < 0;
	}


	/**
	 * Check if this version is the same as the other version.
	 *
	 * @param Version $otherVersion
	 * @return bool
	 */
	public function equals(Version $otherVersion)
	{
		return $this->compare($otherVersion) == 0;
	}


	/**
	 * Check if this version is compatible to another version.
	 * 
	 * @param Version $otherVersion
	 * @return bool
	 */
	public function isCompatible(Version $otherVersion)
	{
		return $this->extensionA == $otherVersion->extensionA;
	}


	/**
	 * Generate a string, represent the version.
	 *
	 * @param bool $blnComplete
	 * @return string
	 */
	public function toString($blnComplete = false)
	{
		$this->validate();
		
		// add major and minor to version
		$strVersion = $this->major . '.' . $this->minor;
		// add revision
		if ($this->revision >= 0)
		{
			$strVersion .= '.' . $this->revision;
		}
		// add extension
		if ($this->extension)
		{
			$strVersion .= '-' . $this->extensionA . ($blnComplete || $this->extensionN > 1 ? $this->extensionN : '');
		}
		// add release and build
		if ($blnComplete || $this->release != 'stable')
		{
			if ($blnComplete || $this->release == 'head')
			{
				$strVersion .= '.' . $this->release . '.' . $this->build;
			}
			else
			{
				$strVersion .= '.' . $this->release . $this->build;
			}
		}
		else
		{
			// add build without release
			$strVersion .= '.' . $this->build;
		}

		return $strVersion;
	}


	/**
	 * Set the major number.
	 *
	 * @param int $major
	 */
	public function setMajor($major)
	{
		$this->major = intval($major);
	}


	/**
	 * Get the major number.
	 *
	 * @return int
	 */
	public function getMajor()
	{
		return $this->major;
	}


	/**
	 * Set the minor number.
	 *
	 * @param int $minor
	 */
	public function setMinor($minor)
	{
		$this->minor = intval($minor);
	}


	/**
	 * Get the minor number.
	 *
	 * @return int
	 */
	public function getMinor()
	{
		return $this->minor;
	}


	/**
	 * Set the revision number.
	 *
	 * @param int $revision
	 */
	public function setRevision($revision)
	{
		$this->revision = intval($revision);
	}


	/**
	 * Get the revision number.
	 *
	 * @return int
	 */
	public function getRevision()
	{
		return $this->revision;
	}


	/**
	 * Set the extension component.
	 *
	 * @param string $extension
	 */
	public function setExtension($extension)
	{
		if (!$extension)
		{
			$this->extension = null;
			$this->extensionA = null;
			$this->extensionN = 1;
		}
		else if (!preg_match('#^([a-zA-Z]+)(\d*)$#', $extension, $match))
		{
			throw new InvalidVersionExtensionException($extension);
		}
		else
		{
			$this->extension  = $extension;
			$this->extensionA = $match[1];
			$this->extensionN = $match[2] ? intval($match[2]) : 1;
		}
	}


	/**
	 * Get the extension component.
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}


	/**
	 * Set the release.
	 *
	 * @param string $release
	 */
	public function setRelease($release)
	{
		if (!in_array($release, array('alpha', 'beta', 'rc', 'stable', 'head')))
			throw new InvalidVersionReleaseException($release);

		$this->release = $release;
	}


	/**
	 * Get the release.
	 *
	 * @return string
	 */
	public function getRelease()
	{
		return $this->release;
	}


	/**
	 * Get the release as comparable number.
	 *
	 * @return int
	 */
	public function getReleaseNumber()
	{
		switch ($this->release)
		{
			case 'head':   return 0;
			case 'alpha':  return 1;
			case 'beta':   return 2;
			case 'rc':     return 3;
			case 'stable': return 4;
			default:       return -1;
		}
	}


	/**
	 * Set the build number.
	 *
	 * @param int $build
	 */
	public function setBuild($build)
	{
		$this->build = $build;
	}


	/**
	 * Get the build number.
	 *
	 * @return int
	 */
	public function getBuild()
	{
		return $this->build;
	}


	/**
	 * Magic __toString()
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString(false);
	}
}


/**
 * Class IncompleteVersionException
 *
 * Exception thrown if a version is incomplete.
 */
class IncompleteVersionException extends Exception
{
	public function __construct($major, $minor, $build)
	{
		parent::__construct('The version is incomplete, a version need at least a major, minor and build number. The current major is ' . $major . ', the current minor is ' . $minor . ' and the current build is ' . $build . '!');
	}
}


/**
 * Class InvalidVersionExtensionException
 *
 * Exception thrown if an invalid extension is set to a version.
 */
class InvalidVersionExtensionException extends Exception
{
	public function __construct($extension)
	{
		parent::__construct('The version extension "' . $extension . '" is invalid, only lowercase or uppercase characters, optionally followed by numbers are allowed!');
	}
}


/**
 * Class InvalidVersionReleaseException
 *
 * Exception thrown if an invalid release is set to a version.
 */
class InvalidVersionReleaseException extends Exception
{
	public function __construct($release)
	{
		parent::__construct('The release "' . $release . '" is invalid, use one of "alpha", "beta", "rc", "stable" or "head"!');
	}
}


/**
 * Class IncompatibleVersionException
 *
 * Exception thrown if two incompatible versions are compared.
 */
class IncompatibleVersionException extends Exception
{
	/**
	 * The first version.
	 * @var Version
	 */
	protected $versionA;


	/**
	 * The second version.
	 * @var Version
	 */
	protected $versionB;


	/**
	 * Create a new exception.
	 *
	 * @param Version $versionA
	 * @param Version $versionB
	 */
	public function __construct(Version $versionA, Version $versionB)
	{
		parent::__construct('Version ' . $versionA . ' is not compatible to version ' . $versionB . '!');
		$this->versionA = new Version($versionA);
		$this->versionB = new Version($versionB);
	}


	/**
	 * Get the first version.
	 * @return Version
	 */
	public function getVersionA()
	{
		return $this->versionA;
	}


	/**
	 * Get the second version.
	 * @return Version
	 */
	public function getVersionB()
	{
		return $this->versionB;
	}
}