import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Bot, Send, User } from 'lucide-react';
import { useState, useRef, useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'AI Assistance',
        href: '/ai',
    },
];

interface Message {
    role: 'user' | 'assistant';
    content: string;
    data?: unknown;
}

export default function AIIndex() {
    const [messages, setMessages] = useState<Message[]>([]);
    const [input, setInput] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const sendMessage = async () => {
        if (!input.trim() || isLoading) return;

        const userMessage = input.trim();
        setInput('');
        setMessages((prev) => [...prev, { role: 'user', content: userMessage }]);
        setIsLoading(true);

        try {
            const response = await fetch('/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    message: userMessage,
                    conversation_history: messages.map((m) => ({
                        role: m.role,
                        content: m.content,
                    })),
                }),
            });

            const data = await response.json();

            if (data.error) {
                setMessages((prev) => [
                    ...prev,
                    { role: 'assistant', content: `Error: ${data.error}` },
                ]);
            } else {
                setMessages((prev) => [
                    ...prev,
                    {
                        role: 'assistant',
                        content: data.response,
                        data: data.data,
                    },
                ]);
            }
        } catch (error) {
            setMessages((prev) => [
                ...prev,
                { role: 'assistant', content: 'Failed to connect to AI service.' },
            ]);
        } finally {
            setIsLoading(false);
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    const renderData = (data: unknown) => {
        if (!data) return null;
        if (Array.isArray(data)) {
            return (
                <div className="mt-2 rounded-lg bg-muted/50 p-3 text-xs">
                    <table className="w-full">
                        <thead>
                            <tr className="border-b border-border">
                                <th className="pb-1 text-left">Invoice #</th>
                                <th className="pb-1 text-left">Customer</th>
                                <th className="pb-1 text-right">Total</th>
                                <th className="pb-1 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.map((item: { invoice_number?: string; customer?: string; total?: number; status?: string }, idx: number) => (
                                <tr key={idx} className="border-b border-border/50 last:border-0">
                                    <td className="py-1">{item.invoice_number}</td>
                                    <td className="py-1">{item.customer}</td>
                                    <td className="py-1 text-right">${item.total}</td>
                                    <td className="py-1">{item.status}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            );
        }
        return (
            <pre className="mt-2 rounded-lg bg-muted/50 p-3 text-xs overflow-x-auto">
                {JSON.stringify(data, null, 2)}
            </pre>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="AI Assistance" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card className="flex flex-1 flex-col">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Bot className="h-5 w-5" />
                            AI Invoice Assistant
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-1 flex-col">
                        <div className="flex-1 overflow-y-auto space-y-4 pr-2 mb-4 min-h-[300px]">
                            {messages.length === 0 && (
                                <div className="flex h-full items-center justify-center text-muted-foreground">
                                    <div className="text-center">
                                        <Bot className="mx-auto h-12 w-12 mb-4 opacity-50" />
                                        <p className="text-lg font-medium">How can I help you today?</p>
                                        <p className="text-sm mt-2">Try asking:</p>
                                        <ul className="text-sm mt-2 space-y-1">
                                            <li>"Show me my recent invoices"</li>
                                            <li>"Create an invoice for Acme Corp for $500 consulting"</li>
                                            <li>"What draft invoices do I have?"</li>
                                        </ul>
                                    </div>
                                </div>
                            )}
                            {messages.map((msg, idx) => (
                                <div
                                    key={idx}
                                    className={`flex gap-3 ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}
                                >
                                    {msg.role === 'assistant' && (
                                        <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                            <Bot className="h-4 w-4" />
                                        </div>
                                    )}
                                    <div
                                        className={`max-w-[80%] rounded-lg px-4 py-2 ${
                                            msg.role === 'user'
                                                ? 'bg-primary text-primary-foreground'
                                                : 'bg-muted'
                                        }`}
                                    >
                                        <p className="whitespace-pre-wrap">{msg.content}</p>
                                        {msg.data && renderData(msg.data as unknown[])}
                                    </div>
                                    {msg.role === 'user' && (
                                        <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-secondary text-secondary-foreground">
                                            <User className="h-4 w-4" />
                                        </div>
                                    )}
                                </div>
                            ))}
                            {isLoading && (
                                <div className="flex gap-3 justify-start">
                                    <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                        <Bot className="h-4 w-4" />
                                    </div>
                                    <div className="rounded-lg bg-muted px-4 py-2">
                                        <div className="flex gap-1">
                                            <span className="animate-bounce">●</span>
                                            <span className="animate-bounce" style={{ animationDelay: '0.1s' }}>●</span>
                                            <span className="animate-bounce" style={{ animationDelay: '0.2s' }}>●</span>
                                        </div>
                                    </div>
                                </div>
                            )}
                            <div ref={messagesEndRef} />
                        </div>
                        <div className="flex gap-2">
                            <Textarea
                                value={input}
                                onChange={(e) => setInput(e.target.value)}
                                onKeyDown={handleKeyDown}
                                placeholder="Type a message..."
                                className="min-h-[60px] resize-none"
                                disabled={isLoading}
                            />
                            <Button
                                onClick={sendMessage}
                                disabled={isLoading || !input.trim()}
                                size="icon"
                                className="h-[60px] w-[60px]"
                            >
                                <Send className="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
