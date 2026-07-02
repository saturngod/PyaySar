import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { format } from 'date-fns';
import { useEffect, useState } from 'react';

interface StatusHistory {
    id: number;
    from_status: string | null;
    to_status: string;
    changed_at: string;
}

interface StatusHistoryDialogProps {
    invoiceId: number | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function StatusHistoryDialog({ invoiceId, open, onOpenChange }: StatusHistoryDialogProps) {
    const [history, setHistory] = useState<StatusHistory[]>([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (open && invoiceId) {
            const timer = setTimeout(() => setLoading(true), 0);
            fetch(`/invoices/${invoiceId}/history`)
                .then((res) => res.json())
                .then((data) => {
                    setHistory(data);
                    setLoading(false);
                })
                .catch((err) => {
                    console.error('Failed to fetch history:', err);
                    setLoading(false);
                });
            return () => clearTimeout(timer);
        }
    }, [open, invoiceId]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>Status History</DialogTitle>
                </DialogHeader>
                <div className="space-y-4">
                    {loading ? (
                        <div className="text-center text-sm text-muted-foreground">Loading history...</div>
                    ) : history.length === 0 ? (
                        <div className="text-center text-sm text-muted-foreground">No history found.</div>
                    ) : (
                        <div className="relative border-l border-border ml-3 space-y-6">
                            {history.map((item) => (
                                <div key={item.id} className="relative pl-6">
                                    <div className="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-background bg-muted" />
                                    <div className="flex flex-col gap-1">
                                        <div className="text-sm font-semibold text-foreground">
                                            {item.from_status ? (
                                                <>
                                                    Changed from <span className="font-medium text-muted-foreground">{item.from_status}</span> to <span className="font-medium text-foreground">{item.to_status}</span>
                                                </>
                                            ) : (
                                                <>
                                                    Set to <span className="font-medium text-foreground">{item.to_status}</span>
                                                </>
                                            )}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {format(new Date(item.changed_at), 'MMM d, yyyy h:mm a')}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
}
