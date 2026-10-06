import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import ts from 'typescript';

const requests = [];
const exports = {};
const source = readFileSync(
    new URL('../../resources/js/lib/refreshUnreadMessages.ts', import.meta.url),
    'utf8',
);
runInNewContext(
    ts.transpileModule(source, {
        compilerOptions: { module: ts.ModuleKind.CommonJS },
    }).outputText,
    {
        exports,
        require: () => ({ router: { reload: (options) => requests.push(options) } }),
    },
);

const { refreshUnreadMessages } = exports;
refreshUnreadMessages();
refreshUnreadMessages();
refreshUnreadMessages();
assert.equal(requests.length, 1, 'Refreshes must not overlap');
assert.deepEqual(Array.from(requests[0].only), ['unreadMessageCount', 'matches']);

requests[0].onFinish();
assert.equal(requests.length, 2, 'A pending read/event must trigger a fresh count');
refreshUnreadMessages();
refreshUnreadMessages();
assert.equal(requests.length, 2);
requests[1].onFinish();
assert.equal(requests.length, 3, 'Events during the follow-up must not be lost');
requests[2].onFinish();
assert.equal(requests.length, 3, 'Finishing without pending events must stop');

refreshUnreadMessages();
assert.equal(requests.length, 4, 'Later events must still refresh');
requests[3].onFinish();
