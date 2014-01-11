# [Admin Page Framework](http://wordpress.org/plugins/admin-page-framework/) #

### Welcome to our GitHub Repository

Admin Page Framework is an open source library for WordPress developed under GPL v2, consisting of a set of PHP classes that provides theme and plugin developers with simpler means of creating administration pages of WordPress.

## Screenshots ##

<p align="center">
	<a href="https://lh6.googleusercontent.com/-bR-r0LvjXOQ/UtDfGRngfGI/AAAAAAAABfQ/P3pWFDgJR30/s0/admin_page_framework_v3.png" title="Admin Page Framework - Text, Password, and Textarea">
		<img src="https://lh6.googleusercontent.com/-bR-r0LvjXOQ/UtDfGRngfGI/AAAAAAAABfQ/P3pWFDgJR30/s600/admin_page_framework_v3.png" alt="Admin Page Framework - Text, Password, and Textarea" />
	</a>
</p>

<div style="margin:20px; float:left">
	<a href="https://lh5.googleusercontent.com/-LWGuI2UbX2I/UtDfIzl9VjI/AAAAAAAABgA/7VQzt3ilB4g/s0/admin_page_framework_v3_selectors.png" title="Admin Page Framework - Selectors">
		<img src="https://lh5.googleusercontent.com/-LWGuI2UbX2I/UtDfIzl9VjI/AAAAAAAABgA/7VQzt3ilB4g/s144/admin_page_framework_v3_selectors.png" alt="Admin Page Framework - Selectors" />
	</a>
	&nbsp;
	<a href="https://lh5.googleusercontent.com/-8AZyx8CRl0E/UtDfILtczJI/AAAAAAAABfw/ngiUiLKwnb8/s0/admin_page_framework_v3_files.png" title="Admin Page Framework - Image, Media Library, and File Uploads">
		<img src="https://lh5.googleusercontent.com/-8AZyx8CRl0E/UtDfILtczJI/AAAAAAAABfw/ngiUiLKwnb8/s144/admin_page_framework_v3_files.png" alt="Admin Page Framework - Image, Media Library, and File Uploads" />
	</a>
	&nbsp;
	<a href="https://lh4.googleusercontent.com/-D4EqxHNoZf8/UtDfHZbij7I/AAAAAAAABfg/SfAOl5WTKOU/s0/admin_page_framework_v3_checklist.png" title="Admin Page Framework - Taxonomies and Post Types Checklist">
		<img src="https://lh4.googleusercontent.com/-D4EqxHNoZf8/UtDfHZbij7I/AAAAAAAABfg/SfAOl5WTKOU/s144/admin_page_framework_v3_checklist.png" alt="Admin Page Framework - Taxonomies and Post Types Checklist" />
	</a>
	&nbsp;
	<a href="https://lh3.googleusercontent.com/-jhy50e9D6J0/UtDfIRoBq5I/AAAAAAAABf8/7Y4tRzZUSsc/s0/admin_page_framework_v3_misc.png" title="Admin Page Framework - Misc">
		<img src="https://lh3.googleusercontent.com/-jhy50e9D6J0/UtDfIRoBq5I/AAAAAAAABf8/7Y4tRzZUSsc/s144/admin_page_framework_v3_misc.png" alt="Admin Page Framework - Misc" />
	</a>
	&nbsp;
	<a href="https://lh4.googleusercontent.com/-MZUbpV_y9x8/UtDfI89MaWI/AAAAAAAABgI/Ji9ki25uHCU/s0/admin_page_framework_v3_verification.png" title="Admin Page Framework - Form Input Verification">
		<img src="https://lh4.googleusercontent.com/-MZUbpV_y9x8/UtDfI89MaWI/AAAAAAAABgI/Ji9ki25uHCU/s144/admin_page_framework_v3_verification.png" alt="Admin Page Framework - Form Input Verification" />
	</a>
	&nbsp;
	<a href="https://lh6.googleusercontent.com/-cmgLpnx3iIA/UtDfHZdsxvI/AAAAAAAABfk/BklgC-MnqWY/s0/admin_page_framework_v3_export_%2526_import.png" title="Admin Page Framework - Export and Import Options">
		<img src="https://lh6.googleusercontent.com/-cmgLpnx3iIA/UtDfHZdsxvI/AAAAAAAABfk/BklgC-MnqWY/s144/admin_page_framework_v3_export_%2526_import.png" alt="Admin Page Framework - Export and Import Options" />
	</a>
	&nbsp;
	<a href="https://lh5.googleusercontent.com/-YujIDW7LMdU/UtDfGcrDjrI/AAAAAAAABfM/EMA4NF3WgYU/s0/admin_page_framework_help_pane.png" title="Admin Page Framework - Contextual Help Pane">
		<img src="https://lh5.googleusercontent.com/-YujIDW7LMdU/UtDfGcrDjrI/AAAAAAAABfM/EMA4NF3WgYU/s144/admin_page_framework_help_pane.png" alt="Admin Page Framework - Contextual Help Pane" />
	</a>
	&nbsp;
	<a href="https://lh4.googleusercontent.com/-aTHPHWneQ9k/UtDfG26gXiI/AAAAAAAABgQ/w5JOtmOJ-4s/s0/admin_page_framework_meta_box_fields.png" title="Admin Page Framework - Custom Post Type and Meta Box">
		<img src="https://lh4.googleusercontent.com/-aTHPHWneQ9k/UtDfG26gXiI/AAAAAAAABgQ/w5JOtmOJ-4s/s144/admin_page_framework_meta_box_fields.png" alt="Admin Page Framework - Custom Post Type and Meta Box" />
	</a>	
</div>

## Installation ##

The latest development version can be downloaded [here](https://github.com/michaeluno/admin-page-framework/archive/master.zip).

It includes the demo plugin which uses the framework and is ready to be installed as a WordPress plugin. Just upload the unpacked folder to the `...\wp-content\plugins` folder then activate it. The sample pages will be created.

## Example ##

```PHP
<?php
/* Plugin Name: Admin Page Framework - Getting Started */ 

if ( ! class_exists( 'AdminPageFramework' ) )
	include_once( dirname( __FILE__ ) . '/class/admin-page-framework.php' );
	
class APF extends AdminPageFramework {

	function setUp() {
		
		$this->setRootMenuPage( 'Settings' );	
		$this->addSubMenuPage(
			'My First Page',	// page and menu title
			'myfirstpage'		// page slug
		);
	
	}

	function do_myfirstpage() {  // do_{page slug}
		?>
		<h3>Say Something</h3>
		<p>This is my first admin page!</p>
		<?php   
	}
	
}
new APF;
// That's it!
```

## Bugs ##
If you find an issue, let us know [here](https://github.com/michaeluno/admin-page-framework/issues)!

## Support ##
This is a developer's portal for Admin Page Framework and should _not_ be used for support. Please visit the [support forums](http://wordpress.org/support/plugin/admin-page-framework).

## Contributions ##
Anyone is welcome to contribute to Admin Page Framework.

There are various ways you can contribute:

1. Raise an [Issue](https://github.com/michaeluno/admin-page-framework/issues) on GitHub.
2. Send us a Pull Request with your bug fixes and/or new features.
3. Provide feedback and suggestions on [enhancements](https://github.com/michaeluno/admin-page-framework/issues?direction=desc&labels=Enhancement&page=1&sort=created&state=open).
4. Improve the [documentation](https://github.com/michaeluno/admin-page-framework/blob/master/documentation_guideline.md).

## Copyright and License ##
Released under the [GPL v2](license.txt) or later.
Copyright © 2014 Michael Uno

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

