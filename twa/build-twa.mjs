import { createRequire } from 'module';
const require = createRequire(import.meta.url);

const { TwaManifest } = require('@bubblewrap/core/dist/lib/TwaManifest');
const { TwaGenerator } = require('@bubblewrap/core/dist/lib/TwaGenerator');
const { GradleWrapper } = require('@bubblewrap/core/dist/lib/GradleWrapper');
const { Config } = require('@bubblewrap/core/dist/lib/Config');
const { JdkHelper } = require('@bubblewrap/core/dist/lib/jdk/JdkHelper');
const { AndroidSdkTools } = require('@bubblewrap/core/dist/lib/androidSdk/AndroidSdkTools');
const { ConsoleLog } = require('@bubblewrap/core/dist/lib/Log');
const path = require('path');
const fs = require('fs');

const PROJECT_DIR = path.resolve('.');
const log = new ConsoleLog('build');

async function main() {
  // Load bubblewrap config
  console.log('Loading bubblewrap config...');
  const configPath = path.join(process.env.HOME, '.bubblewrap', 'config.json');
  const config = Config.deserialize(fs.readFileSync(configPath, 'utf-8'));

  // Create TwaManifest from web manifest
  console.log('Fetching web manifest...');
  let twaManifest = await TwaManifest.fromWebManifest('https://artifact.stewardgoods.com/manifest.json');

  // Override settings
  twaManifest.packageId = 'com.stewardgoods.artifact';
  twaManifest.appVersionCode = 1;
  twaManifest.appVersionName = '1.0.0';
  twaManifest.signingKey = {
    path: path.resolve('keystore.jks'),
    alias: 'artifact-manager',
  };
  twaManifest.fallbackType = 'customtabs';
  twaManifest.enableSiteSettingsShortcut = true;
  twaManifest.splashScreenFadeOutDuration = 300;
  twaManifest.minSdkVersion = 21;

  // Save the twa-manifest.json
  const manifestJson = JSON.stringify(twaManifest.toJson(), null, 2);
  fs.writeFileSync(path.join(PROJECT_DIR, 'twa-manifest.json'), manifestJson);
  console.log('Saved twa-manifest.json');

  // Generate TWA project
  console.log('Generating TWA Android project...');
  const generator = new TwaGenerator();
  await generator.createTwaProject(PROJECT_DIR, twaManifest, log);
  console.log('Project generated.');

  // Build APK
  console.log('Building APK...');
  const jdkHelper = new JdkHelper(process, config);
  const androidSdkTools = await AndroidSdkTools.create(process, config, jdkHelper, log);
  // Build tools already installed, skip installBuildTools()

  const gradleWrapper = new GradleWrapper(process, androidSdkTools, PROJECT_DIR);
  await gradleWrapper.assembleRelease();
  console.log('Build complete!');

  // Sign the APK
  console.log('Signing APK...');
  const unsignedApk = path.join(PROJECT_DIR, 'app', 'build', 'outputs', 'apk', 'release', 'app-release-unsigned.apk');
  const signedApk = path.join(PROJECT_DIR, 'artifact-manager.apk');

  // Use apksigner from the Android SDK
  const buildToolsDir = path.join(config.androidSdkPath, 'build-tools');
  const btVersions = fs.readdirSync(buildToolsDir).sort();
  const latestBt = btVersions[btVersions.length - 1];
  const apksignerPath = path.join(buildToolsDir, latestBt, 'apksigner');
  const zipalignPath = path.join(buildToolsDir, latestBt, 'zipalign');

  // Zipalign first
  const alignedApk = unsignedApk.replace('unsigned', 'aligned');
  const { execSync } = require('child_process');
  const env = jdkHelper.getEnv();

  execSync(`"${zipalignPath}" -v -p 4 "${unsignedApk}" "${alignedApk}"`, { env, stdio: 'inherit' });

  // Sign with apksigner
  execSync(
    `"${apksignerPath}" sign --ks "${path.resolve('keystore.jks')}" --ks-key-alias artifact-manager --ks-pass pass:artifact-manager-twa --key-pass pass:artifact-manager-twa --out "${signedApk}" "${alignedApk}"`,
    { env, stdio: 'inherit' }
  );

  console.log(`\nAPK ready: ${signedApk}`);
}

main().catch(err => {
  console.error('Build failed:', err);
  process.exit(1);
});
