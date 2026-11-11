#!/bin/bash

# Constants
# Text colours
RESET='\033[0m'

BLACK='\033[0;30m'
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[0;37m'

# Bold colours
BOLD_RED='\033[1;31m'
BOLD_GREEN='\033[1;32m'
BOLD_YELLOW='\033[1;33m'
BOLD_BLUE='\033[1;34m'

# Formatting
BOLD='\033[1m'
DIM='\033[2m'
UNDERLINE='\033[4m'

# Clear screen
clear
printf '\033[3J'

echo -e "${GREEN}GENERATING DISTRIBUTION SET${RESET}\n"

# Stop if anything goes wrong.
set -euo pipefail

# --------------------------------------------------
# Configuration
# --------------------------------------------------

PLUGIN_SLUG="skyrocket-wordpress-utilities"
MAIN_FILE="${PLUGIN_SLUG}.php"
CHANGELOG_FILE="CHANGELOG.md"

# --------------------------------------------------
# Locate plugin root
# --------------------------------------------------

# This script lives in /tools, so move one directory up.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

cd "$PLUGIN_ROOT"

# --------------------------------------------------
# Check Git repository
# --------------------------------------------------

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "Error: Plugin directory is not a Git repository."
    exit 1
fi

if [[ ! -f "$MAIN_FILE" ]]; then
    echo "Error: Could not find ${MAIN_FILE}."
    exit 1
fi

if [[ ! -f "$CHANGELOG_FILE" ]]; then
    echo "Error: Could not find ${CHANGELOG_FILE}."
    exit 1
fi

# --------------------------------------------------
# Extract and validate plugin versions
# --------------------------------------------------

# 1. Extract version from the plugin header
VERSION=$(sed -nE \
    's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([^[:space:]]+).*/\1/p' \
    "$MAIN_FILE" | head -n 1)

if [[ -z "$VERSION" ]]; then
    echo "Error: Could not determine plugin version from header in ${MAIN_FILE}."
    exit 1
fi

# 2. Extract version from the SWU_VERSION constant definition
CONST_VERSION=$(sed -nE \
    "s/^[[:space:]]*define\([[:space:]]*['\"]SWU_VERSION[''][[:space:]]*,[[:space:]]*['\"]([^'\"]+)[''][[:space:]]*\).*/\1/p" \
    "$MAIN_FILE" | head -n 1)

if [[ -z "$CONST_VERSION" ]]; then
    echo "Error: Could not determine SWU_VERSION constant from ${MAIN_FILE}."
    exit 1
fi

# 3. Ensure both versions match
if [[ "$VERSION" != "$CONST_VERSION" ]]; then
    echo -e "Error: ${CYAN}VERSION MISMATCH${RESET} in plugin main PHP file detected!\n"
    echo -e "  - Plugin Header Version:  ${RED}${VERSION}${RESET}"
    echo -e "  - SWU_VERSION Constant:   ${RED}${CONST_VERSION}${RESET}"
    echo -e "\nPlease update these so they match before building.\n"
    exit 1
fi

# --------------------------------------------------
# Validate Changelog entry
# --------------------------------------------------

# Check if CHANGELOG.md contains an H2 header for this version (e.g., "## 1.0.5 - ...")
# Regex breakdown: Matches lines starting with "## <version> " followed by any description characters
if ! grep -qE "^## ${VERSION}[[:space:]]+-" "$CHANGELOG_FILE"; then
    echo -e "Error: ${CYAN}CHANGELOG ENTRY MISSING${RESET} for version ${RED}${VERSION}${RESET}!\n"
    echo -e "Please ensure ${CHANGELOG_FILE} has an H2 entry starting with:\n"
    echo -e "${GREEN}## ${VERSION} - [Release Description Here]${RESET}\n"
    exit 1
fi

# --------------------------------------------------
# Confirm with user
# --------------------------------------------------

echo
printf "You are creating a distribution set for ${CYAN}version ${VERSION}${RESET}.\n\nAre you sure you want to continue? (Y/N): "
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
    echo "Commit your changes before building the release ZIP. This ensures the ZIP contains exactly the committed files."
    echo
    exit 1
fi

# --------------------------------------------------
# Build release ZIP (with translation cleanup)
# --------------------------------------------------

DIST_DIR="${PLUGIN_ROOT}/dist"
ZIP_FILE="${DIST_DIR}/${PLUGIN_SLUG}.zip"

mkdir -p "$DIST_DIR"

# Remove any previous ZIP.
rm -f "$ZIP_FILE"

echo "Building plugin release..."
echo "Plugin:  ${PLUGIN_SLUG}"
echo "Version: ${VERSION}"
echo

# Create a secure temporary staging directory
TEMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TEMP_DIR"' EXIT

# Create a subdirectory inside temp matching the plugin slug for proper WordPress structure
STAGING_DIR="${TEMP_DIR}/${PLUGIN_SLUG}"
mkdir -p "$STAGING_DIR"

# Export repository tree to the staging directory
git archive --format=tar HEAD | tar -x -C "$STAGING_DIR"

# Purge non-English localization files (.po and .mo)
echo "Cleaning up translation files..."
echo
find "${STAGING_DIR}" -type f \( -name "*.po" -o -name "*.mo" \) -delete

# Pack the staging directory into the final release ZIP (outputting skyrocket-wordpress-utilities.zip)
(
    cd "$TEMP_DIR"
    zip -r -q "$ZIP_FILE" "$PLUGIN_SLUG"
)

echo -e "${GREEN}Release ZIP created successfully${RESET}:\n"
echo "$ZIP_FILE"
echo