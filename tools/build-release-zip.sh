#!/bin/bash

# Stop if anything goes wrong.
set -euo pipefail

# --------------------------------------------------
# Constants
# --------------------------------------------------

# Text colours.
RESET='\033[0m'
RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'

# Clear screen.
clear
printf '\033[3J'

echo -e "${GREEN}GENERATING DISTRIBUTION SET${RESET}\n"

# --------------------------------------------------
# Locate plugin root & configuration
# --------------------------------------------------

# This script lives in /tools, so move one directory up.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

# Derive plugin slug from the parent folder name.
PLUGIN_SLUG="$(basename "$PLUGIN_ROOT")"
MAIN_FILE="${PLUGIN_SLUG}.php"
CHANGELOG_FILE="CHANGELOG.md"

cd "$PLUGIN_ROOT"

# Verify that the matching PHP file exists in the plugin root.
if [[ ! -f "$MAIN_FILE" ]]; then
    echo -e "${RED}Error:${RESET} Could not find the plugin file '${MAIN_FILE}' matching the folder name '${PLUGIN_SLUG}'."
    echo "Please ensure the plugin folder name matches the main plugin PHP filename."
    exit 1
fi

# --------------------------------------------------
# Check Git repository
# --------------------------------------------------

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "Error: Plugin directory is not a Git repository."
    exit 1
fi

if [[ ! -f "$CHANGELOG_FILE" ]]; then
    echo "Error: Could not find ${CHANGELOG_FILE}."
    exit 1
fi

# --------------------------------------------------
# Validate GitHub Updater dependency
# --------------------------------------------------

UPDATER_BOOTSTRAP="includes/plugin-updater.php"
UPDATER_VENDOR_CLASS="vendor/lavamitts/github-plugin-updater/src/Updater.php"

if [[ ! -f "$UPDATER_BOOTSTRAP" ]]; then
    echo -e "${RED}Error:${RESET} Could not find ${UPDATER_BOOTSTRAP}."
    exit 1
fi

# Ensure the bootstrap file is committed.
if ! git ls-files --error-unmatch "$UPDATER_BOOTSTRAP" >/dev/null 2>&1; then
    echo -e "${RED}Error:${RESET} ${UPDATER_BOOTSTRAP} is not tracked by Git."
    echo "Stage and commit ${UPDATER_BOOTSTRAP} before building the distribution."
    exit 1
fi

# Ensure the Composer package is installed locally.
if [[ ! -f "$UPDATER_VENDOR_CLASS" ]]; then
    echo -e "${RED}Error:${RESET} Could not find ${UPDATER_VENDOR_CLASS}."
    echo "Run 'composer install' before building the distribution."
    exit 1
fi

# --------------------------------------------------
# Extract and validate plugin version
# --------------------------------------------------

# Extract version from the plugin header.
VERSION=$(sed -nE \
    's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([^[:space:]]+).*/\1/p' \
    "$MAIN_FILE" | head -n 1)

if [[ -z "$VERSION" ]]; then
    echo "Error: Could not determine plugin version from header in ${MAIN_FILE}."
    exit 1
fi

# --------------------------------------------------
# Validate changelog entry
# --------------------------------------------------

# Check for an H2 entry for this version.
if ! grep -Fq "## ${VERSION} -" "$CHANGELOG_FILE"; then
    echo -e "Error: ${CYAN}CHANGELOG ENTRY MISSING${RESET} for version ${RED}${VERSION}${RESET}!\n"
    echo -e "Please ensure ${CHANGELOG_FILE} has an H2 entry starting with:\n"
    echo -e "${GREEN}## ${VERSION} - [Release Description Here]${RESET}\n"
    exit 1
fi

# --------------------------------------------------
# Confirm with user
# --------------------------------------------------

echo
printf "You are creating a distribution set for ${CYAN}version ${VERSION}${RESET} of ${CYAN}${PLUGIN_SLUG}${RESET}.\n\nAre you sure you want to continue? (Y/N): "

read -n 1 -r
echo
echo

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Build aborted."
    exit 0
fi

# --------------------------------------------------
# Check working tree
# --------------------------------------------------

if [[ -n "$(git status --porcelain)" ]]; then
    echo -e "${RED}ERROR${RESET}\n\nYour Git working tree is not clean."
    echo
    echo "Commit your changes before building the release ZIP."
    echo "This ensures the ZIP contains exactly the committed files."
    echo
    exit 1
fi

# --------------------------------------------------
# Build release ZIP
# --------------------------------------------------

DIST_DIR="${PLUGIN_ROOT}/dist"
ZIP_FILE="${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip"

mkdir -p "$DIST_DIR"

# Remove any previous ZIP for this version.
rm -f "$ZIP_FILE"

echo "Building plugin release..."
echo "Plugin:  ${PLUGIN_SLUG}"
echo "Version: ${VERSION}"
echo

# Create a secure temporary staging directory.
TEMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TEMP_DIR"' EXIT

# Create a subdirectory matching the unversioned plugin slug.
STAGING_DIR="${TEMP_DIR}/${PLUGIN_SLUG}"
mkdir -p "$STAGING_DIR"

# Export the committed repository tree.
git archive --format=tar HEAD | tar -x -C "$STAGING_DIR"

# Inject the single-file Updater class from Composer's vendor directory.
mkdir -p "${STAGING_DIR}/includes"
cp "$UPDATER_VENDOR_CLASS" "${STAGING_DIR}/includes/Updater.php"

# --------------------------------------------------
# Clean distribution files
# --------------------------------------------------

# Remove non-English localization files (.po and .mo).
echo "Cleaning up translation files..."

find "${STAGING_DIR}" -type f \( -name "*.po" -o -name "*.mo" \) -delete

# Remove Composer-generated files and development dependencies.
echo "Removing Composer development files..."

rm -rf "${STAGING_DIR}/vendor"
rm -f "${STAGING_DIR}/composer.json"
rm -f "${STAGING_DIR}/composer.lock"

# Remove development tools and GitHub configuration.
echo "Removing development and Git files..."

rm -rf "${STAGING_DIR}/tools"
rm -rf "${STAGING_DIR}/.github"
rm -f "${STAGING_DIR}/.gitignore"

echo

# --------------------------------------------------
# Create distribution ZIP
# --------------------------------------------------

# Pack the plugin directory into the versioned release ZIP.
(
    cd "$TEMP_DIR"
    zip -r -q "$ZIP_FILE" "$PLUGIN_SLUG"
)

echo -e "${GREEN}Release ZIP created successfully${RESET}:\n"
echo "$ZIP_FILE"
echo