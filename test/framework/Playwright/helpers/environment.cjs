const fs = require('node:fs');
const path = require('node:path');

class EnvironmentInterruptedError extends Error {
    constructor(message) {
        super(message);
        this.name = 'EnvironmentInterruptedError';
    }
}

function defaultWorkspaceRoot() {
    return path.resolve(__dirname, '../../../../../../../');
}

function resolveWorkspacePath(...segments) {
    return path.join(defaultWorkspaceRoot(), ...segments);
}

function requireExistingPath(label, candidate) {
    if (!candidate || !fs.existsSync(candidate)) {
        throw new EnvironmentInterruptedError(`${label} is not available at ${candidate || '(empty path)'}. Configure a replacement or install the required local application.`);
    }

    return candidate;
}

function isEnvironmentInterrupted(error) {
    return error instanceof EnvironmentInterruptedError || error?.name === 'EnvironmentInterruptedError';
}

module.exports = {
    EnvironmentInterruptedError,
    defaultWorkspaceRoot,
    isEnvironmentInterrupted,
    requireExistingPath,
    resolveWorkspacePath,
};
