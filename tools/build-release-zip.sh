#!/bin/bash

# Stop if anything goes wrong.
set -euo pipefail

# --------------------------------------------------
# Configuration
# --------------------------------------------------

PLUGIN_SLUG="skyrocket-wordpress-utilities"
MAIN_FILE="${PLUGIN_SLUG}.php"

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

# --------------------------------------------------
# Check working tree
# --------------------------------------------------

if [[ -n "$(git status --porcelain)" ]]; then
    echo "Error: Your Git working tree is not clean."
    echo
    echo "Commit your changes before building the release ZIP."
    echo "This ensures the ZIP contains exactly the committed files."
    exit 1
fi

# --------------------------------------------------
# Extract plugin version
# --------------------------------------------------

VERSION=$(sed -nE \
    's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([^[:space:]]+).*/\1/p' \
    "$MAIN_FILE" | head -n 1)

if [[ -z "$VERSION" ]]; then
    echo "Error: Could not determine plugin version from ${MAIN_FILE}."
    exit 1
fi

# --------------------------------------------------
# Build release ZIP (with translation cleanup)
# --------------------------------------------------

DIST_DIR="${PLUGIN_ROOT}/dist"
ZIP_FILE="${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip"

mkdir -p "$DIST_DIR"

# Remove any previous ZIP for this version.
rm -f "$ZIP_FILE"

echo
echo "Building plugin release..."
echo "Plugin:  ${PLUGIN_SLUG}"
echo "Version: ${VERSION}"
echo

# Create a secure temporary staging directory
TEMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TEMP_DIR"' EXIT

# Export repository tree to the temp directory
git archive --format=tar HEAD | tar -x -C "$TEMP_DIR"

# Purge non-English localization files (.po and .mo)
echo "Cleaning up translation files..."
find "${TEMP_DIR}" -type f \( -name "*.po" -o -name "*.mo" \) -delete

# Pack the cleaned staging directory into the final release ZIP
(
    cd "$TEMP_DIR"
    zip -r -q "$ZIP_FILE" .
)

echo "Release ZIP created successfully:"
echo "$ZIP_FILE"
echo