Packages
========

Versioning
----------

The versioning is based on the classic version numbering scheme with extensions.

A version number is combined like the following.

**Version number in EBNF**
```
DOT            = <the dot (".") character>
UPALPHA        = <any US-ASCII upper case letter "A".."Z">
LOALPHA        = <any US-ASCII lower case letter "a".."z">
DIGIT          = <any US-ASCII digit "0".."9">
ALPHA          = UPALPHA | LOALPHA
ALNUM          = ALPHA | DIGIT
MAJOR          = DIGIT
MINOR          = DIGIT
REVISION       = DIGIT
EXTENSION      = <"-"> *ALPHA *DIGIT
BUILD          = *DIGIT
HEADBUILD      = <"head"> [DOT *ALNUM]
REGULARBUILD   = <"alpha" DIGIT | "beta" DIGIT | "rc" DIGIT | "stable"> [DOT BUILD]
RELEASE        = HEADBUILD | REGULARBUILD

VERSION = MAJOR DOT MINOR [DOT REVISION] [EXTENSION] DOT RELEASE
```

The only mandatory fields in the version are major, minor and the release.
The release number is combined of either the word "head" in conjunction with an alpha numeric revision suffix which refers to unstable releases or one of the words "alpha", "beta", "rc" or "stable" suffixed with one digit. The latter one can also be accompanied with a build number.
The revision number (also known as "maintenance" or "patch level") is entirely optional, as it is not used in most cases.

The optional extension component is mostly useful for personal repositories, which serve altered versions of certain extensions. This is known from derivative works like the Ubuntu project, which is releasing altered debian packages with a suffix "ubuntu1" or similar.

Please note that developers are encouraged to provide adjustments to upstream maintainers in favor of providing own versions of packages though to keep the forking of extensions to a minimum.

A real world versioning example
-------------------------------

Let's imagine an agency has altered an extension for ACME Inc. and is providing this extension via their own repository.
To eliminate problems with the vanilla extension in the main repository, the agency appends the extension "acme" to the version and results in the version
`1.2.3-acme1`.

Using this scheme one can prevent unwanted upgrading (see \ref{sec:versioncompatibility}).

In contrast to the other components of the version, the build number is the only one that can not be influenced by the developer. The build number is incremented by one for every package being uploaded into the repository but not when the description or the properties are changing. This is in contrast to the old ER, which incremented the build number upon every change to the extension.
\todo{How shall this work? version bumping can only be done upon "building" of a package but the upload is much later in the work flow.}

Version compatibility
---------------------

Thanks to the `extension` component of the version, it is possible to tag modified variants of an extension. These variants are in most cases incompatible to newer (vanilla) releases of the same extension.

To address these potentional problems, the following version compatibility rules are in place.

Versions are compatible if, and only if, any of the following conditions is met:
* ...the alphabetic representation of the `extension` component is identical.
* ...the extension component is omitted on both versions.

<table>
<tr>
<td>**Version 1**</td>  <td>**=/!**</td>  <td>**Version 2**</td>
</tr>
<tr>
<td>1.2.3</td>        <td>=</td>      <td>4.5.6</td>
</tr>
<tr>
<td>1.2-extA.3</td>   <td>=</td>      <td>4.5-extA.6</td>
</tr>
<tr>
<td>1.2-extB.3</td>   <td>!</td>      <td>4.5-extC.6</td>
</tr>
<tr>
<td>1.2-extD3.4</td>  <td>=</td>      <td>5.6-extD7.8</td>
</tr>
<tr>
<td>1.2-extE3.4</td>  <td>!</td>      <td>5.6-extF7.8</td>
</tr>

Please note that this only refers to the compatibility in respect to the version and that the compatibility can be further restricted by the extension meta file as described in \ref{sec:package information file}.

Version comparison
------------------

When comparing compatible versions (according to the rules defined in \ref{sec:versioncompatibility}), one has to compare versions with extension component with a different approach than those without extension component.

### Version comparison without extension component

A version without extension component will be matched according to the usual mechanism.

A given version A is bigger (aka. newer) than version B if, and only if, one of the following conditions is true:
* A.major>B.major
* A.major=B.major \& A.minor>B.minor
* A.major=B.major \& A.minor=B.minor \& A.revision>B.revision
* A.major=B.major \& A.minor=B.minor \& A.revision=B.revision \& A.build>B.build

A given version A ist identical to version B if, and only if:
* A.major=B.major \& A.minor=B.minor \& A.revision=B.revision \& A.build=B.build

### Version comparison with extension component

If an `extension` component is present, the following additional rules are to be applied in addition to the rules mentioned in \ref{sec:version comparison without extension}.

A given version A is bigger (aka. newer) than version B if, and only if:
* A.extensionA = B.extensionA \& A.extensionN > B.extensionN

A given version A ist identical to version B if, and only if:
* A.extensionA = B.extensionA \& A.extensionN = B.extensionN

At this, one must pay attention to the fact that the alphabetical representation and the numeric representation has to be evaluated separately.

If the alphabetical representation (extensionA) is different for two versions, they have to be regarded as incompatible according to \ref{sec:versioncompatibility}. When no numeric representation (extensionN) is present in a version with extension, the version has to be evaluated as if a numeric part has been set with the value of "1".

**Version comparison example**
<table>
<td>**field**</td><td>**Version 1**</td><td>**Version 2**</td>
<td>full version</td><td>1.2-acme3.4</td><td>1.2-acme4.1</td>
<td>major</td><td>1</td><td>1</td>
<td>minor</td><td>2</td><td>2</td>
<td>extension</td><td>acme3</td><td>acme4</td>
<td>extensionA (alpha)</td><td>acme</td><td>acme</td>
<td>extensionN (numeric)</td><td>3</td><td>4</td>
</table>

Package structure
-----------------

Extensions are shipped in a package file. Such a package contains meta information, optional package management routines for installation, un-installation and handling upgrades in addition to the application data.

The package file is a zip file with a changed extension to mark it as a package file. This approach is already known \ie from java (jar files).

For the file extension currently the following are considered (on the right hand are the file formats listed that are known to use this file extension):

<table>
<td>.cext</td><td>none known so far</td>
<td>.cex</td><td>INMOS Transputer Development System Occam User Program, ThumbsPlus File, The Currency Exchanger Rate File, CLAN Output File (Child Language Data Exchange System)</td>
<td>.ctp</td><td>American Greetings CreataCard (Broderbund), BestAddress HTML Editor Combined Template File (Multimedia Australia Pty. Ltd.)</td>
<td>.ctx</td><td>CTX Chemical File (Gasteiger Group), Chinese Character Input File, Compressed Text, GE Industrial Systems CIMPLICITY Text Version HMI Screen, Alphacam Compiled Text (Planit), Online Course Text (Microsoft Corporation), Pretty Good Privacy (PGP) Ciphertext File (PGP Corporation), Windows Terminal Server INI Backup File (Microsoft Corporation), Visual Basic User Control Binary File, CTRAN/W DEFINE Compressed Data File (Geo-Slope International), Nokia PC Suite Backup Contact File (Nokia)</td>
</table>

A package contains the following files:
* PACKAGE - package information (required, see \ref{sec:package information file})
* FILES - list of files contained in the package (required for certification, see \ref{sec:files information file})
* LICENSE files - the license files for the extension (required according to \ref{sec:package information file})
* ... any files the developer might need below the virtual root "CONTAO" which refers to the contao installation root.
* ... any files the developer might need below the virtual root "DATA" for the user data root.

PACKAGE information file
------------------------

The XML format is described in the schema file extension.xsd\supref{lst:extension.xsd}.

As usage example serves extension.xml\supref{lst:extension.xml}.

### Package relationships - dependencies, conflicts and preferations

A relationship of an extension to another extension can be defined in form of a `dependency`, a `conflict` or a `preferation`.
Each of these relationships can be towards any version of the mentioned extension or even be narrowed down to certain version numbers.

### Versioning of relationships

As dependencies and conflicts are often regarding certain versions or a certain range of versions.

In the definition of an relationship, we may use the following attributes:
* `match` which means an exact match on the version (X=Y).
* `lowest` which is implicit inclusive and tells the lower end of the range (X>=Y).
* `highest` which is implicit exclusive and tells the upper end of the range (X<Y).

The attributes `lowest` and `highest` do not have to be used in combination, therefore it is possible to define an open range.
When used in combination, they define a closed range.

The attributes `lowest` and `highest` must not be used in conjunction with the attribute `match`. If used in conjunction with `match`, the exact match takes precedence over the other attributes and therefore is always exact.

In the package information file, this might look like the following when defining a conflict for a certain db driver (in this case mysql).

**XML example: conflict example mysql**
```xml
...
<ctx:conflict name="db:mysql">
    <ctx:version match="4"/>
    <ctx:version lowest="5" highest="5.1"/>
    <ctx:version lowest="5.2"/>
</ctx:conflict>
...
```

In above example definition we have a conflict for:
* all version 4 drivers.
* all versions 5 to 5.1 ( 5<=X<5.1)
* all versions from 5.2 on upwards (5.2<=X)
With above condition we define a conflict for all versions of MySQL except for version 5.1 (we could have done it more simple though).

### Handling of versions with extension component

In general the extension component of a version is ignored as it makes common sense that an altered version of an extension is compatible to it's unaltered counterpart from an external point of view. This behaviour can be altered by defining the extension version in the relationship explicitly. If specified, the match for the version must be performed including the extension part.

### Special relationships

Package relationships can not only refer other packages but some other name-spaces as well.
This is useful to restrict dependency on some php features, a certain database driver, some PEAR package or something else that has been defined in the specification.

Currently the following name spaces exist.
<table>
<td>contao</td><td>The Contao core.</td>
<td>php:[feature]</td><td>Relates to a certain php feature. See php features in</td>\ref{sec:package relationship name spaces php features}
<td>pear:[package]</td><td>Relates to a certain pear package. See pear packages in</td>\ref{sec:package relationship name spaces pear packages}.
<td>db:[type]</td><td>Relates to a certain database,</td>\ie db:mysql. See database drivers in \ref{sec:package relationship name spaces database drivers}.
</table>

Alternative dependencies
------------------------

Often it is required to have a certain feature available but multiple ways exist to get this feature available when depending on some PHP feature but to also provide a wrapper class as workaround if it is not available.

Instead of defining features within the repository client and therefore having to update the client all the time when a new feature has been defined, we provide the developer with the possibility to define alternative paths by specifying groups of dependencies.

When defining groups of dependencies, the first positive match found is accepted and the group evaluation will be stopped. Note that the order in the XML has no meaning and therefore the attribute `preferation` must be used to define which dependency is the most preferable.

**XML example: alternative relationships**
```xml
...
<ctx:group preferation="php:soap nusoap">
<ctx:dependency name="php:soap">
    <ctx:description>I love soap, thats because i need this php extension.</ctx:description>
</ctx:dependency>
<ctx:dependency name="nusoap">
    <ctx:description>If you dont have soap, I pleased with an alternative implementation.</ctx:description>
</ctx:dependency>
</ctx:group>
...
<table>

This example describes, that it depends on the PHP SOAP extension but alternatively can also use the extension "nusoap" if the first is not available.

Upgrade and downgrade compatibility
-----------------------------------

In many cases upgrades and downgrades may cause problems. This is even more the case, when the data schema is changing.
That's the reason why the developer needs a way to define what potential threat an upgrade or downgrade of a certain package implies.

This potentional threat can be categorized in the following sections:

<table>
<td>**Category**</td><td>**threat factor**</td><td>**Description**</td>
<td>lossless</td><td>none</td><td>Up- and downgrade can be performed without data loss.</td>
<td>dropUnused</td><td>almost none</td><td>Up- and downgrade will remove data that will not be in use any more.</td>
<td>partial</td><td>might be dangerous</td><td>Up- and downgrade might remove user data. Therefore manual intervention is suggested.</td>
<td>undefined</td><td>dragons live here!</td>&This setting is implicit defined when the developer has not defined a setting at all. The result might be user data loss, in worst case scenario resulting in total data loss.
</table>

Whenever a developer has not defined a compatibility, the following automatic assumptions are made:
When upgrading, the compatibility is defaulting to "partial".
When downgrading, the compatibility is defaulting to "undefined".
The extension repository manager will then display a message where the up/downgrade compatibility is shown in addition to the description text of the compatibility.

As "undefined" is an implicit type, the developer can not add a description text. For "undefined" compatibility the extension repository client will provide two possibilities:
* soft-update - upgrading without removing any user data (hence by updating the schema still some data might get lost).
* hard-update - remove the old version including all user data and reinstall the other version then (possibly the worst attempt).

A corresponding upgrade/downgrade compatibility might look like the following:

**XML example: upgrade/downgrade compatibility**
```xml
...
<ctx:extension version="1.2.3">
    <ctx:upgrade version="1.1" userdata="lossless"/>
    <ctx:upgrade version="1.0" userdata="partial"/>
    <ctx:downgrade version="1.2" userdata="lossless"/>
    <ctx:downgrade version="1.1" userdata="dropUnused"/>
    <ctx:downgrade version="1.0" userdata="partial"/>
</ctx:extension>
...
```

When defining such compatibilities, always the most recent version is defined for upgrading and the most oldest version for downgrading.

In above example we have the following definitions:
<table>
<td>**Rule**</td><td>**Version**</td><td>**Description**</td>
<td>upgrade</td><td>1.1</td><td>All versions 1.1 <= X < 1.2.3 can be updated without data loss.</td>
<td>upgrade</td><td>1.0</td><td>All versions 1.0 <= X < 1.1 can be updated with partial data loss.</td>
<td>downgrade</td><td>1.2</td><td>To all versions 1.2 <= X < 1.2.3 can be downgraded without data loss.</td>
<td>downgrade</td><td>1.1</td><td>To all versions 1.1 <= X < 1.2 can be downgraded and unused data will be removed.</td>
<td>downgrade</td><td>1.0</td><td>To all versions 1.0 <= X < 1.1 can be downgraded with loss of user data.</td>
</table>

The ordering of information in the xml tree is of no relevance as all items have to be sorted by the client application and sorted. Then the ranges will be defined and the proper information has to be retrieved.

FILES - package contents information file
-----------------------------------------

The package contents information contains a list of all files contained within the package (except for the information file "FILES" itself).
The file is used for validation of the installation and especially needed when creating a signed package file to validate the integrity of the installation.
When creating an unsigned package, the file is not required and therefore may be omitted.

The file syntax is simple.
Every line is made up of a sha1 hash followed by a space character and the full file path specifying the destination.

**FILES example: package contents information file**
```
...
4543a0fe56f27277948469c15284ac25 CONTAO/system/modules/myExtension/Class1.php
a692ffb1c6026092d916872b2114941c CONTAO/system/modules/myExtension/Class2.php
2709656afab36a8f635206c6956cbf53 CONTAO/system/modules/myExtension/templates/class1\_template.tpl
3fced4c880f491dce98c116266361f1c CONTAO/system/modules/myExtension/templates/class2\_template.tpl
b52f2d57d10c4f7ee67a7eb9615d5d24 LGPL
2e7781b7b08ab489e08526f43adf5b0c PACKAGE
...
```

Package relationship name spaces
--------------------------------

### PHP features

this section is currently undocumented

### PEAR features

this section is currently undocumented

### Database driver

this section is currently undocumented
