/**
 * webpack.config.js
 *
 * @license   License GNU General Public License version 2 or later; see LICENSE.txt
 * @author    Andrea Gentil - Anibal Sanchez <team@extly.com>
 * @copyright (c)2012-2020 Extly, CB. All rights reserved.
 *
 * Modified by ClickFWD
 */

// Array of Webpack plugins
let buildPlugins = [];

// Extension directories to be visited
const extensionTypesDirs = [
  'plugins',
];

const pluginTemplateFiles = [
  'yoyo.php',
  'yoyo-demo-widget.php'
]

// WARNING - Clean these development folders before building
const globalCleanDevAssets = [
  /\/node_modules\//,
  /\/vendor\//,
  /\.DS_Store/,
];

// Required Webpack plugins
const CopyWebpackPlugin = require('copy-webpack-plugin');
const Dotenv = require('dotenv-webpack');
const FileManagerPlugin = require('filemanager-webpack-plugin');
const fs = require('fs');
const fsExtra = require('fs-extra');
const moment = require('moment');
const path = require('path');
const readDirRecursive = require('fs-readdir-recursive');
const ZipFilesPlugin = require('webpack-zip-files-plugin');
const glob = require("glob");
const touch = require("touch");
const { exit } = require('process');

let definitions;
const releaseDate = moment()
  .format('YYYY-MM-DD');
const year = moment()
  .format('YYYY');
const releaseDir = 'build/release';
const releaseDirAbs = path.resolve(__dirname, releaseDir);
const templatesDir = 'build/templates';
const renderDirectories = [templatesDir];
const allExtensionTypesDirs = extensionTypesDirs;

const tagTransformation = (content) => content
  .toString()
  .replace(/\[COPYRIGHT\]/g, definitions.COPYRIGHT)
  .replace(/\[AUTHOR_EMAIL\]/g, definitions.AUTHOR_EMAIL)
  .replace(/\[AUTHOR_URL\]/g, definitions.AUTHOR_URL)
  .replace(/\[AUTHOR\]/g, definitions.AUTHOR)
  .replace(/\[PLUGIN_URL\]/g, definitions.PLUGIN_URL)
  .replace(/\[PLUGIN_ALIAS\]/g, definitions.PLUGIN_ALIAS)
  .replace(/\[PLUGIN_DESC\]/g, definitions.PLUGIN_DESC)
  .replace(/\[PLUGIN_NAME\]/g, definitions.PLUGIN_NAME)
  .replace(/\[LICENSE\]/g, definitions.LICENSE)
  .replace(/\[LICENSE\]/g, definitions.LICENSE)
  .replace(/\[RELEASE_VERSION\]/g, definitions.RELEASE_VERSION)
   .replace(/\[DATE\]/g, releaseDate)
  .replace(/\[YEAR\]/g, year);

function loadEnvironmentDefinitions() {
  const defs = {};

  const env = new Dotenv();
  Object.keys(env.definitions)
    .forEach((definition) => {
      const key = definition.replace('process.env.', '');
      let value = env.definitions[definition];

      value = value.replace(/^"(.+(?="$))"$/, '$1');
      value = value.replace(/%CR%/g, '\n');
      value = value.replace(/%TAB%/g, '\t');

      defs[key] = value;
    });

  return defs;
}

function cleanDevAssets() {
  const cleanDevAssetsDirs = allExtensionTypesDirs.map(
      // Read all files
      (extensionTypesDir) => glob.sync(
        path.resolve(__dirname, extensionTypesDir) + '/**/*', {
          dot: true
        }
      )
    )
    // One flat array
    .flat()
    // Filter to files that match the globalCleanDevAssets to clean
    .filter((item) => {
      return globalCleanDevAssets.find((globalCleanDevFolder) => {
        return globalCleanDevFolder.test(item);
      });
    });

  cleanDevAssetsDirs.map((file) => fsExtra.removeSync(file));
}

function removeReleaseDirectory() {
  return new FileManagerPlugin({
    onStart: {
      delete: [
        releaseDirAbs,
      ],
      mkdir: [
        releaseDirAbs,
      ],
    }
  });
}

function discoverFilesToRender(tplDirectory, extensionType) {
  const tplPath = `${tplDirectory}/${extensionType}/`;
  const absTplPath = `${__dirname}/${tplPath}`;

  return glob.sync(
      path.resolve(__dirname, `${tplPath}**/*.@(ini|xml|php|css|js)`), 
      {
        follow: true
      }
    )
    .map(
      (file) => file.replace(absTplPath, '')
    );
}

function discoverManifestTemplates(tplDirectory, extensionType) {
  const tplPath = `${tplDirectory}/${extensionType}/`;
  const absTplPath = `${__dirname}/${tplPath}`;

  return glob.sync(
      path.resolve(__dirname, `${tplPath}**/*.xml`),
    )
    .map(
      (file) => file.replace(absTplPath, '')
    );
}

function resolveExtensionTemplate(tplDirectory, extensionType) {
  return path.resolve(
    __dirname,
    `${tplDirectory}/${extensionType}`,
  );
}

function renderTemplates() {
  const renderTpls = [];

  // For build templates directories
  renderDirectories.forEach((tplDirectory) => {
    // For all extension types
    allExtensionTypesDirs.forEach((extensionType) => {
      const extTplDir = resolveExtensionTemplate(tplDirectory, extensionType);
      const templates = discoverFilesToRender(tplDirectory, extensionType);
      
      // For each template
      templates.forEach((file) => {

        const pathParts = file.split('/');
        if (pathParts[1] == 'vendor' && pathParts[4] === 'vendor') {
          return;
        }
        if (pathParts[1] == 'vendor' && pathParts[4] === 'tests') {
          return;
        }

        const dest = path.resolve(__dirname, `${extensionType}/${file}`);
        const item = {
          context: extTplDir,
          from: file,
          to: dest,
          transform: tagTransformation,
        };

        // Render each template
        renderTpls.push(item);
      });
    });
  });

  return new CopyWebpackPlugin(renderTpls);
}

function declareZipsGeneration() {
  const zipDirectories = [templatesDir];
  const zipPlugins = [];

  // For each templates directory to be zipped
  zipDirectories.forEach((tplDirectory) => {
    // For all extension types
    extensionTypesDirs.forEach((extensionType) => {
      const extZipDir = resolveExtensionTemplate(tplDirectory, extensionType);
      const templates = discoverFilesToRender(tplDirectory, extensionType);

      // For each template
      templates.forEach((tplFile) => {
        const srcFile = path.resolve(__dirname, `${extensionType}/${tplFile}`);
        const srcDir = path.dirname(srcFile);
        const extname = path.extname(srcFile);
        const pluginName = path.basename(tplFile, extname);

        if (!pluginTemplateFiles.includes(path.basename(srcFile))) return;

        const manifestTplFile = `${extZipDir}/${tplFile}`;
        const extensionTplDir = path.dirname(manifestTplFile);
        const parts = extensionTplDir.split('/');
        const extElement = parts.pop();

        const outputFile = path.resolve(
          __dirname,
          `${releaseDir}/${pluginName}_v${definitions.RELEASE_VERSION}`,
        );

        const zipFile = {
          entries: [{
            src: srcDir,
            dist: pluginName,
          }],
          output: outputFile,
          format: 'zip',
        };

        // Define the zip
        const itemZip = new ZipFilesPlugin(zipFile);
        zipPlugins.push(itemZip);
      });
    });
  });

  return zipPlugins;
}

// Let's build something

// Ensure that there is a .gitkeep, the webpack runs "packing" .gitkeep
touch('.gitkeep');

// Global constant definitions (.env)
definitions = loadEnvironmentDefinitions();

// Clean the development assets before packing
cleanDevAssets();

// Start clean
buildPlugins.push(removeReleaseDirectory());

// Render the manifests
buildPlugins.push(renderTemplates());

// Just define the zips with everything
buildPlugins = buildPlugins.concat(declareZipsGeneration());

// We are ready, Webpack generate!
module.exports = {
  entry: './.gitkeep',
  output: {
    filename: '.gitkeep',
    path: path.resolve(__dirname, releaseDir),
  },

  plugins: buildPlugins,
};
