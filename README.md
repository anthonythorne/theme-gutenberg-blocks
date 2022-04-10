# Theme Gutenberg Blocks

This is a boilerplate for adding multiple blocks within a theme. If you wish to add blocks to a plugin rather
than via the theme please use [Plugin Gutenberg Blocks](https://github.com/anthonythorne/plugin-gutenberg-blocks).

## Table of Contents

1. [Directory Structure](#directory-structure)
1. [Getting started](#getting-started)
1. [Customisation](#customisation)
    1. [Webpack](#customisation-webpack)
    1. [New Blocks](#customisation-new-blocks)
    1. [Multiple vs Combined Block Asset Files](#customisation-multiplevs-vs-combined-block-asset-files)
1. [Build](#build)
1. [TODO](#todo)


## <a id="directory-structure">Directory Structure</a>

The following is the basic directory structure.

├── `/blocks` - All the custom blocks that will be automatically registered.<br />
├── `/build` - Default directory that the block bundles get added to from the `wp-script` commands within the
`package.json`.<br />
├── `/node_modules` - Bundles.<br />
├── `/src` - Contains the index.js file that is the source for the bundling commands, this automatically imports all the
  custom blocks from the `/blocks` directory, assuming they contain a `index.js` file.<br />
├── `/blocks-bootstrap.php` - The PHP code that runs the WP functions to automatically register blocks from the `/blocks`
  directory. This uses glob patterns to autoload blocks, see [New Blocks](#customisation-new-blocks)<br />
├── `/package.json` - The package json.<br />
├── `/package-lock.json` - The package json lock file.<br />
├── `/README.md` - This file.<br />
└── `/webpack.config.js` - The webpack configuration that extends on the
`node_modules/@wordpress/scripts/config/webpack.config.js` Config.<br />


## <a id="getting-started">Getting Started</a>

This section details the beginning of the setup within your theme.

1. Place a copy of this directory within your theme, this example assumes that this directory will be placed
   at `themes/xxx/source/gutenberg`. If your case differs, please place it wherever is convenient and update the
   following bootstrap loader.
1. Update the `functions.php` file of your theme to include;

```php
$theme_gutenberg_blocks_bootstrap_file = dirname( __FILE__ ) . '/source/gutenberg/blocks-bootstrap.php';

if ( file_exists( $theme_gutenberg_blocks_bootstrap_file ) ) {
    require_once $theme_gutenberg_blocks_bootstrap_file;
}
```

1. Navigate to the directory and run the commands.

```bash
cd wp-content/themes/xxx/source/gutenberg
npm install
npm run build
```

## <a id="customisation">Customisation</a>

From this point on we will refer to the directory `themes/xxx/source/gutenberg` as `gutenberg`. Within the `gutenberg`
directory there are several customisations that can be done.


### <a id="customisation-webpack">Webpack</a>

The `gutenberg/webpack.config.js` file in this directory extends the default located here
`node_modules/@wordpress/scripts/config/webpack.config.js`. Any additional webpack customisations that extend or overwrites the
WordPress scripts default should go in this file.


### <a id="customisation-new-blocks">New Blocks</a>

within `gutenberg/blocks` directory lays all the custom blocks that will be bundled. The directory has,
`first-block`, `second-block` and `third-block`. They can be renamed or removed, they are simply there to provide an example.

Every custom block will reside within their own directory within `gutenberg/blocks` directory. The format is as per
WordPress current information(WP 5.9.3) see
[How-to Guides / Blocks](https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/). In this instance
each block does not need any package bundler files as this is handled in the `gutenberg` root directory.


#### Required
Each block requires a directory of its own, named after its block, i.e. `first-block`. Within this directory the following
files can be used;<br />
└── `/blocks`<br />
&nbsp;&nbsp;└── `/block-name`<br />
&nbsp;&nbsp;&nbsp;&nbsp;├── `/args.php` - Optional. Not a WordPress default. This is used within the bootstrap file
when auto registering all the blocks. This is optional and can be removed. See `register_block_type() @param $args`.<br />
&nbsp;&nbsp;&nbsp;&nbsp;├── `/block.json` - The settings and other metadata should be defined in a block.json file.<br />
&nbsp;&nbsp;&nbsp;&nbsp;├── `/edit.js` - The edit functionality, which is a component that is shown in the editor when the
block gets inserted.<br />
&nbsp;&nbsp;&nbsp;&nbsp;├── `/editor.scss` - Contains any CSS code that gets applied to the editor.<br />
&nbsp;&nbsp;&nbsp;&nbsp;├── `/index.js` - Registers a new block, combining asset files within said block directory.<br />
&nbsp;&nbsp;&nbsp;&nbsp;├── `/save.js` - The save function defines the way in which the different attributes should be
combined into the final markup, which is then serialized by the block editor into `post_content`.<br />
&nbsp;&nbsp;&nbsp;&nbsp;└── `/style.scss` - Applied both on the front of your site and in the editor.<br />


#### Register the new block
That's it, the block will be auto registered based on the above files, and the block will be auto
bundled next time the script is run from the this `gutenberg` root directory.


### <a id="customisation-multiplevs-vs-combined-block-asset-files">Multiple vs Combined Block Asset Files</a>

Currently, the webpack file has a variable to change the way that the build is done. This variable is defined as `multipleEntryPoint`,
which is a boolean and if its true the webpack builder will create asset files for each block and not combine them.
One caveat with this is that the block.json file for each block needs to point at the correct asset files that are built.

The examples in this directory are using the multiple entry points, so each block has individual asset files, this can be
seen within these lines;
```json
{
  "editorScript": "file:../../build/first-block.js",
  "editorStyle": "file:../../build/first-block.css",
  "style": "file:../../build/style-first-block.css"
}
```

The opposite would be that `multipleEntryPoint` is set to `false` within the `webpack.config.js` file, then the
block.json would have lines like this;
```json
{
  "editorScript": "file:../../build/index.js",
  "editorStyle": "file:../../build/index.css",
  "style": "file:../../build/style-index.css"
}
```

## <a id="build">Build</a>
If your site runs a build on deploy, its advisable that you gitignore the `./build` dir and add the script to your
pipelines or similar build process.


## <a id="todo">TODO</a>
The following would be nice to address at some point, and advice would be welcome.

* SIs there a way that the variable used within the webpack file could be globally set `multipleEntryPoint`,
  so that it's passed to the `webpack.config.js` for the entry points. In addition it would be used to automatically
  update the block.json files for each of the custom blocks, dictating the "editorScript", "editorStyle", and "style"
  keys.