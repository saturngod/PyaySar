import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Head, router, usePage } from '@inertiajs/react';
import { Copy, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Token {
    id: number;
    name: string;
    last_used_at: string | null;
    created_at: string;
}

interface Props {
    tokens: Token[];
    new_token?: string;
}

export default function ApiTokens({ tokens, new_token }: Props) {
    const { flash } = usePage().props as { flash?: { new_token?: string } };
    const [tokenName, setTokenName] = useState('');
    const [creating, setCreating] = useState(false);
    const [copied, setCopied] = useState(false);
    const shownToken = new_token || flash?.new_token;

    const handleCreate = (e: React.FormEvent) => {
        e.preventDefault();
        setCreating(true);
        router.post('/settings/api-tokens', { name: tokenName }, {
            onFinish: () => {
                setCreating(false);
                setTokenName('');
            },
        });
    };

    const handleDelete = (tokenId: number) => {
        if (confirm('Delete this token? Any app using it will stop working.')) {
            router.delete(`/settings/api-tokens/${tokenId}`);
        }
    };

    const handleCopy = () => {
        if (shownToken) {
            navigator.clipboard.writeText(shownToken);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'API Tokens', href: '/settings/api-tokens' }]}>
            <Head title="API Tokens" />
            <SettingsLayout>
                <div className="space-y-8">
                    <div>
                        <h2 className="text-lg font-semibold">API Tokens</h2>
                        <p className="text-sm text-muted-foreground mt-1">
                            Create tokens to connect AI tools (Claude Desktop, Cursor, etc.) to your Pyaysar account via MCP.
                        </p>
                    </div>

                    {/* New token alert */}
                    {shownToken && (
                        <div className="rounded-lg border border-green-500/30 bg-green-500/10 p-4 space-y-2">
                            <p className="text-sm font-medium text-green-700 dark:text-green-400">
                                Token created! Copy it now — you won't see it again.
                            </p>
                            <div className="flex items-center gap-2">
                                <code className="flex-1 rounded bg-muted px-3 py-2 text-xs break-all font-mono">
                                    {shownToken}
                                </code>
                                <Button size="sm" variant="outline" onClick={handleCopy}>
                                    {copied ? 'Copied!' : <Copy className="h-4 w-4" />}
                                </Button>
                            </div>
                        </div>
                    )}

                    {/* Create form */}
                    <form onSubmit={handleCreate} className="flex items-end gap-3">
                        <div className="flex-1 space-y-1">
                            <Label htmlFor="token-name">Token name</Label>
                            <Input
                                id="token-name"
                                value={tokenName}
                                onChange={(e) => setTokenName(e.target.value)}
                                placeholder="e.g. Claude Desktop"
                                required
                            />
                        </div>
                        <Button type="submit" disabled={creating || !tokenName.trim()}>
                            {creating ? 'Creating...' : 'Create Token'}
                        </Button>
                    </form>

                    {/* Token list */}
                    <div className="space-y-3">
                        <h3 className="text-sm font-medium text-muted-foreground">Your tokens</h3>
                        {tokens.length === 0 ? (
                            <p className="text-sm text-muted-foreground py-4">No tokens yet. Create one above.</p>
                        ) : (
                            <div className="space-y-2">
                                {tokens.map((token) => (
                                    <div
                                        key={token.id}
                                        className="flex items-center justify-between rounded-lg border px-4 py-3"
                                    >
                                        <div>
                                            <p className="text-sm font-medium">{token.name}</p>
                                            <p className="text-xs text-muted-foreground">
                                                Created {new Date(token.created_at).toLocaleDateString()}
                                                {token.last_used_at && (
                                                    <> · Last used {new Date(token.last_used_at).toLocaleDateString()}</>
                                                )}
                                            </p>
                                        </div>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => handleDelete(token.id)}
                                            className="text-muted-foreground hover:text-red-500"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* How to use */}
                    <div className="rounded-lg border p-4 space-y-3">
                        <h3 className="text-sm font-medium">How to use</h3>
                        <div className="text-sm text-muted-foreground space-y-2">
                            <p><strong>1.</strong> Create a token above.</p>
                            <p><strong>2.</strong> Copy the token.</p>
                            <p><strong>3.</strong> Add it to your AI client config:</p>
                            <pre className="rounded bg-muted p-3 text-xs overflow-x-auto">
{`{
  "mcpServers": {
    "pyaysar": {
      "url": "https://your-domain.com/mcp",
      "headers": {
        "Authorization": "Bearer YOUR_TOKEN"
      }
    }
  }
}`}
                            </pre>
                            <p>For local development, see <code>docs/mcp.md</code>.</p>
                        </div>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
