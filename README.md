<p align="center">
    <a href="" rel="noopener">
        <img width="700" height="400" src="https://github.com/OUGC-Network/OUGC-Thread-Contributors/assets/1786584/d5f06e8a-5850-4836-8a83-b7b5b4f2ecf5" alt="Project logo">
    </a>
</p>

<h3 align="center">OUGC Thread Contributors</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![GitHub Issues](https://img.shields.io/github/issues/OUGC-Network/OUGC-Thread-Contributors.svg)](./issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/OUGC-Network/OUGC-Thread-Contributors-Media.svg)](./pulls)
[![License](https://img.shields.io/badge/license-GPL-blue)](/LICENSE)

</div>

---

<p align="center"> Displays a list of users who contributed to a discussion.
    <br> 
</p>

## 📜 Table of Contents <a name = "table_of_contents"></a>

- [About](#about)
- [Getting Started](#getting_started)
	- [Dependencies](#dependencies)
	- [File Structure](#file_structure)
	- [Install](#install)
	- [Update](#update)
	- [Template Modifications](#template_modifications)
- [Settings](#settings)
- [Usage](#usage)
- [Built Using](#built_using)
- [Authors](#authors)
- [Acknowledgments](#acknowledgement)
- [Support & Feedback](#support)

## 🚀 About <a name = "about"></a>

OUGC Thread Contributors is a MyBB plugin designed to enhance user engagement and provide a comprehensive overview of thread participants. With this simple plugin, users can easily access a list of contributors who have posted or commented within a thread. The list ensures an accurate representation of participant involvement, fostering greater interaction and community engagement

[Go up to Table of Contents](#table_of_contents)

## 📍 Getting Started <a name = "getting_started"></a>

The following information will assist you into getting a copy of this plugin up and running on your forum.

### Dependencies <a name = "dependencies"></a>

A setup that meets the following requirements is necessary to use this plugin.

- [MyBB](https://mybb.com/) >= 1.8.30
- PHP >= 7.4
- [MyBB-PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) >= 13

### File structure <a name = "file_structure"></a>

  ```
   .
   ├── inc
   │ ├── plugins
   │ │ ├── ougc
   │ │ │ ├── ThreadContributors
   │ │ │ │ ├── templates
   │ │ │ │ │ ├── .html
   │ │ │ │ │ ├── user.html
   │ │ │ │ │ ├── user_avatar.html
   │ │ │ │ │ ├── user_plain.html
   │ │ │ │ ├── settings.json
   │ │ ├── ougc_threadcontributors.php.php
   │ ├── languages
   │ │ ├── espanol
   │ │ │ ├── admin
   │ │ │ │ ├── ougc_threadcontributors.lang.php
   │ │ │ ├── ougc_threadcontributors.lang.php
   │ │ ├── english
   │ │ │ ├── admin
   │ │ │ │ ├── ougc_threadcontributors.lang.php
   │ │ │ ├── ougc_threadcontributors.lang.php
   ```

### Installing <a name = "install"></a>

Follow the next steps in order to install a copy of this plugin on your forum.

1. Download the latest package from the [MyBB Extend](https://community.mybb.com/mods.php?action=view&pid=1361) site or from the [repository releases](https://github.com/OUGC-Network/OUGC-Thread-Contributors/releases/latest).
2. Upload the contents of the _Upload_ folder to your MyBB root directory.
3. Browse to _Configuration » Plugins_ and install this plugin by clicking _Install & Activate_.

### Updating <a name = "update"></a>

Follow the next steps in order to update your copy of this plugin.

1. Browse to _Configuration » Plugins_ and deactivate this plugin by clicking _Deactivate_.
2. Follow step 1 and 2 from the [Install](#install) section.
3. Browse to _Configuration » Plugins_ and activate this plugin by clicking _Activate_.

### Template Modifications <a name = "template_modifications"></a>

To display the list of contributors it is required that you edit the `showthread` template for each of your themes.

1. Open the `header` template for editing.
2. Find `{$usersbrowsing}`.
3. Add `{$ougc_threadcontributors_list}` right after.
4. Save the template.

[Go up to Table of Contents](#table_of_contents)

## 🛠 Settings <a name = "settings"></a>

Below you can find a description of the plugin settings.

### Global Settings

- **Show Avatars** `yesNo` Default: `no`
	- _If disabled, a list of usernames will be displayed._
- **Show Avatars to Guests** `yesNo` Default: `no`
	- _If disabled, a list of usernames will be displayed to guests._
- **Order By** `radio` Default: `username`
	- _Order criteria to use: ordering by username could be faster._
- **Order On Ascending Order** `yesNo` Default: `no`
	- _If enabled, the order of users will be reversed._
- **Ignore Authors** `yesNo` Default: `no`
	- _If enabled, the thread author will not be displayed in the contributors list._
- **Avatar Maximum Dimensions** `numeric` Default: `30`
	- _Set the maximum avatar width and height size._
- **Allow Post Filtering (Experimental)** `yesNo` Default: `no`
	- _If enabled, user list will link to the same thread filtered to display the user's posts. If disabled, the
	  filtering wil not be accessible._

[Go up to Table of Contents](#table_of_contents)

## 📖 Usage <a name="usage"></a>

This plugin has no additional configurations; after activating make sure to modify the global settings in order to get this plugin working.

[Go up to Table of Contents](#table_of_contents)

## ⛏ Built Using <a name = "built_using"></a>

- [MyBB](https://mybb.com/) - Web Framework
- [MyBB PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) - A collection of useful functions for MyBB
- [PHP](https://www.php.net/) - Server Environment

[Go up to Table of Contents](#table_of_contents)

## ✍️ Authors <a name = "authors"></a>

- [@Omar G](https://github.com/Sama34) - Idea & Initial work

See also the list of [contributors](https://github.com/OUGC-Network/OUGC-Thread-Contributors/contributors) who participated in this project.

[Go up to Table of Contents](#table_of_contents)

## 🎉 Acknowledgements <a name = "acknowledgement"></a>

- [The Documentation Compendium](https://github.com/kylelobo/The-Documentation-Compendium)

[Go up to Table of Contents](#table_of_contents)

## 🎈 Support & Feedback <a name="support"></a>

This is free development and any contribution is welcome. Get support or leave feedback at the official [MyBB Community](https://community.mybb.com/thread-227574.html).

Thanks for downloading and using our plugins!

[Go up to Table of Contents](#table_of_contents)