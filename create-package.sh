#!/bin/bash

# SliderHome Plugin Package Creator
# Creates a distributable tar.gz package for the OJS SliderHome plugin

set -e  # Exit on error

# Get the plugin directory (where this script is located)
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_NAME="sliderHome"

# Extract version from version.xml
VERSION=$(grep -oP '(?<=<release>)[^<]+' "${PLUGIN_DIR}/version.xml")

# Package name
PACKAGE_NAME="${PLUGIN_NAME}-${VERSION}"
PACKAGE_FILE="${PACKAGE_NAME}.tar.gz"

# Temporary directory for packaging
TEMP_DIR=$(mktemp -d)
PACKAGE_DIR="${TEMP_DIR}/${PLUGIN_NAME}"

echo "=========================================="
echo "SliderHome Plugin Package Creator"
echo "=========================================="
echo "Plugin: ${PLUGIN_NAME}"
echo "Version: ${VERSION}"
echo "Package: ${PACKAGE_FILE}"
echo "=========================================="

# Create package directory
mkdir -p "${PACKAGE_DIR}"

# Copy files, excluding development and build artifacts
echo "Copying plugin files..."

# Use cp with exclusions
cp -r "${PLUGIN_DIR}"/* "${PACKAGE_DIR}/"

# Remove excluded files and directories
echo "Removing development files..."
rm -rf "${PACKAGE_DIR}/node_modules"
rm -rf "${PACKAGE_DIR}/.git"
rm -f "${PACKAGE_DIR}/.gitignore"
rm -f "${PACKAGE_DIR}/.nvmrc"
rm -f "${PACKAGE_DIR}"/*.log
rm -f "${PACKAGE_DIR}"/create-package.sh
rm -f "${PACKAGE_DIR}"/*.tar.gz
rm -rf "${PACKAGE_DIR}/.vscode"
rm -rf "${PACKAGE_DIR}/.idea"

# Navigate to temp directory
cd "${TEMP_DIR}"

# Create tar.gz package
echo "Creating package ${PACKAGE_FILE}..."
tar -czf "${PACKAGE_FILE}" "${PLUGIN_NAME}"

# Move package to plugin directory
mv "${PACKAGE_FILE}" "${PLUGIN_DIR}/"

# Cleanup
rm -rf "${TEMP_DIR}"

echo "=========================================="
echo "Package created successfully!"
echo "Location: ${PLUGIN_DIR}/${PACKAGE_FILE}"
echo "=========================================="
echo ""
echo "Package contents:"
tar -tzf "${PLUGIN_DIR}/${PACKAGE_FILE}" | head -20
TOTAL_FILES=$(tar -tzf "${PLUGIN_DIR}/${PACKAGE_FILE}" | wc -l)
echo "... (${TOTAL_FILES} files total)"
echo ""
