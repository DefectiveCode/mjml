import { afterEach, expect, test } from 'bun:test';
import { existsSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const createdPaths = [];

afterEach(() => {
    while (createdPaths.length > 0) {
        const path = createdPaths.pop();

        if (existsSync(path)) {
            rmSync(path, { force: true, recursive: true });
        }
    }
});

test('renders inline MJML content', async () => {
    const result = await runMjml([mjml('Hello Inline'), '{}']);

    expect(result.exitCode).toBe(0);
    expect(result.stdout).toContain('Hello Inline');
    expect(result.stderr).toBe('');
});

test('renders MJML content from a file argument', async () => {
    const filePath = createTempMjmlFile('Hello File');
    const result = await runMjml([`--mjml-file=${filePath}`, '{}']);

    expect(result.exitCode).toBe(0);
    expect(result.stdout).toContain('Hello File');
    expect(result.stderr).toBe('');
});

test('passes options to file argument renders', async () => {
    const filePath = createTempMjmlFile('Hello Beautify');
    const result = await runMjml([`--mjml-file=${filePath}`, '{"beautify":true}']);

    expect(result.exitCode).toBe(0);
    expect(result.stdout).toContain('Hello Beautify');
});

test('maps beautify options before formatting output', async () => {
    const result = await runMjml([
        mjml('Hello Custom Beautify'),
        '{"beautify":true,"beautifyOptions":{"indentSize":6,"wrapAttributesIndentSize":6,"maxPreserveNewline":0,"preserveNewlines":false}}',
    ]);

    expect(result.exitCode).toBe(0);
    expect(result.stdout).toContain('Hello Custom Beautify');
    expect(result.stdout).toContain('\n      <title>');
    expect(result.stderr).toBe('');
});

test('strips wrapper-only options before rendering', async () => {
    const result = await runMjml([mjml('Hello Wrapper Options'), '{"minify":true,"beautify":false,"beautifyOptions":{"indentSize":8}}']);

    expect(result.exitCode).toBe(0);
    expect(result.stdout).toContain('Hello Wrapper Options');
    expect(result.stderr).toBe('');
});

test('fails when no MJML content is provided', async () => {
    const result = await runMjml([]);

    expect(result.exitCode).toBe(1);
    expect(result.stdout).toContain('No MJML content provided.');
    expect(result.stderr).toBe('');
});

test('fails when a file argument has no path', async () => {
    const result = await runMjml(['--mjml-file=', '{}']);

    expect(result.exitCode).toBe(1);
    expect(result.stdout).toContain('No MJML input file path provided.');
    expect(result.stderr).toBe('');
});

test('fails when a file argument points to a missing file', async () => {
    const result = await runMjml([`--mjml-file=${join(tmpdir(), 'mjml-missing-input-file')}`, '{}']);

    expect(result.exitCode).toBe(1);
    expect(result.stdout).toContain('ENOENT');
    expect(result.stderr).toBe('');
});

test('fails when options are invalid JSON', async () => {
    const result = await runMjml([mjml('Hello Invalid JSON'), '{bad-json']);

    expect(result.exitCode).toBe(1);
    expect(result.stdout).toContain('JSON Parse error');
    expect(result.stderr).toBe('');
});

test('fails when MJML validation fails', async () => {
    const result = await runMjml([invalidMjml(), '{"validationLevel":"strict"}']);

    expect(result.exitCode).toBe(1);
    expect(result.stdout).toContain('ValidationError');
    expect(result.stdout).toContain('mj-column cannot be used inside mj-column');
    expect(result.stderr).toBe('');
});

async function runMjml(args) {
    const process = Bun.spawn(['bun', 'build/mjml.mjs', ...args], {
        cwd: import.meta.dir.replace(/\/build$/, ''),
        stderr: 'pipe',
        stdout: 'pipe',
    });

    const [stdout, stderr, exitCode] = await Promise.all([new Response(process.stdout).text(), new Response(process.stderr).text(), process.exited]);

    return {
        exitCode,
        stderr,
        stdout,
    };
}

function createTempMjmlFile(text) {
    const directory = mkdtempSync(join(tmpdir(), 'mjml-test-'));
    const filePath = join(directory, 'email.mjml');

    createdPaths.push(directory);
    writeFileSync(filePath, mjml(text));

    return filePath;
}

function mjml(text) {
    return `<mjml><mj-body><mj-section><mj-column><mj-text>${text}</mj-text></mj-column></mj-section></mj-body></mjml>`;
}

function invalidMjml() {
    return '<mjml><mj-body><mj-section><mj-column><mj-column><mj-text>Bad</mj-text></mj-column></mj-column></mj-section></mj-body></mjml>';
}
