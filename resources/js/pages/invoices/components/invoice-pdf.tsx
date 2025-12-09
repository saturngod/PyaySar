import { Document, Font, Image, Page, pdf, StyleSheet, Text, View } from '@react-pdf/renderer';
import { format } from 'date-fns';
import { Invoice } from './invoice-form';

// Register MyanmarSagar for Unicode support
Font.register({
    family: 'MyanmarSagar',
    fonts: [
        {
            src: window.location.origin + '/fonts/MyanmarSagar.ttf',
            fontWeight: 'normal',
        }
    ],
});


const styles = StyleSheet.create({
    page: {
        padding: 40,
        fontSize: 10,
        fontFamily: 'MyanmarSagar',
        color: '#111827',
    },
    Text: {
        fontFamily: 'MyanmarSagar',
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 30,
        paddingBottom: 20,
        borderBottomWidth: 1,
        borderBottomColor: '#e5e7eb',
        borderBottomStyle: 'dashed',
    },
    invoiceNo: {
        fontSize: 18,
        fontWeight: 'bold',
    },
    label: {
        fontSize: 8,
        color: '#9ca3af',
        textTransform: 'uppercase',
        marginBottom: 4,
        fontWeight: 'bold',
    },
    dateRow: {
        flexDirection: 'row',
        gap: 20,
    },
    dateItem: {
        minWidth: 100,
    },
    dateValue: {
        fontSize: 10,
        color: '#111827',
    },
    parties: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 30,
        paddingBottom: 30,
        borderBottomWidth: 1,
        borderBottomColor: '#e5e7eb',
        borderBottomStyle: 'dashed',
    },
    partySection: {
        width: '45%',
    },
    partyName: {
        fontSize: 14,
        fontWeight: 'bold',
        marginBottom: 4,
    },
    partyEmail: {
        fontSize: 10,
        color: '#6b7280',
        marginBottom: 8,
    },
    partyAddress: {
        fontSize: 9,
        color: '#9ca3af',
        lineHeight: 1.4,
    },
    logo: {
        width: 50,
        height: 50,
        marginBottom: 10,
        borderRadius: 25,
    },
    itemsHeader: {
        flexDirection: 'row',
        paddingBottom: 8,
        marginBottom: 8,
        borderBottomWidth: 1,
        borderBottomColor: '#e5e7eb',
    },
    itemsHeaderText: {
        fontSize: 8,
        color: '#9ca3af',
        textTransform: 'uppercase',
        fontWeight: 'bold',
        fontFamily: 'MyanmarSagar',
    },
    itemRow: {
        flexDirection: 'row',
        paddingVertical: 8,
        borderBottomWidth: 1,
        borderBottomColor: '#f3f4f6',
    },
    colDesc: { width: '50%' },
    colQty: { width: '15%', textAlign: 'right' },
    colPrice: { width: '17%', textAlign: 'right' },
    colAmount: { width: '18%', textAlign: 'right' },
    itemName: {
        fontWeight: 'bold',
        marginBottom: 2,
        fontFamily: 'MyanmarSagar',
    },
    itemDescription: {
        fontSize: 9,
        color: '#6b7280',
        fontFamily: 'MyanmarSagar',
    },
    footer: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginTop: 20,
    },
    notesSection: {
        width: '50%',
    },
    notesText: {
        fontSize: 9,
        color: '#6b7280',
        lineHeight: 1.4,
    },
    totalsSection: {
        width: '35%',
    },
    totalRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 8,
    },
    totalLabel: {
        color: '#6b7280',
    },
    totalValue: {
        fontWeight: 'bold',
    },
    grandTotal: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        paddingTop: 8,
        borderTopWidth: 1,
        borderTopColor: '#e5e7eb',
        borderTopStyle: 'dashed',
    },
    grandTotalLabel: {
        fontWeight: 'bold',
    },
    grandTotalValue: {
        fontWeight: 'bold',
        fontSize: 12,
    },
});

interface InvoicePdfProps {
    invoice: Invoice;
    userPreference?: {
        company_name?: string;
        company_email?: string;
        company_address?: string;
        company_logo?: string;
    } | null;
}

const InvoicePdfDocument = ({ invoice, userPreference }: InvoicePdfProps) => {
    const customer = invoice.customer;
    const items = invoice.items || [];
    const subTotal = items.reduce((acc, item) => acc + (Number(item.qty) * Number(item.price)), 0);
    const total = subTotal;

    return (
        <Document>
            <Page size="A4" style={styles.page}>
                {/* Header */}
                <View style={styles.header}>
                    <View>
                        <Text style={styles.label}>Invoice No</Text>
                        <Text style={styles.invoiceNo}>{invoice.invoice_number || `INV-${invoice.id}`}</Text>
                    </View>
                    <View style={styles.dateRow}>
                        <View style={styles.dateItem}>
                            <Text style={styles.label}>Issued</Text>
                            <Text style={styles.dateValue}>
                                {invoice.open_date ? format(new Date(invoice.open_date), 'PPP') : '-'}
                            </Text>
                        </View>
                        {invoice.due_date && (
                            <View style={styles.dateItem}>
                                <Text style={styles.label}>Due</Text>
                                <Text style={styles.dateValue}>
                                    {format(new Date(invoice.due_date), 'PPP')}
                                </Text>
                            </View>
                        )}
                    </View>
                </View>

                {/* From / To */}
                <View style={styles.parties}>
                    <View style={styles.partySection}>
                        <Text style={styles.label}>From</Text>
                        {userPreference?.company_logo && (
                            <Image
                                style={styles.logo}
                                src={`/storage/${userPreference.company_logo}`}
                            />
                        )}
                        <Text style={styles.partyName}>{userPreference?.company_name || ''}</Text>
                        <Text style={styles.partyEmail}>{userPreference?.company_email || ''}</Text>
                        <Text style={styles.partyAddress}>{userPreference?.company_address || ''}</Text>
                    </View>
                    <View style={styles.partySection}>
                        <Text style={styles.label}>To</Text>
                        <Text style={styles.partyName}>{customer?.name || ''}</Text>
                        <Text style={styles.partyEmail}>{customer?.email || ''}</Text>
                        <Text style={styles.partyAddress}>{customer?.address || ''}</Text>
                    </View>
                </View>

                {/* Items Header */}
                <View style={styles.itemsHeader}>
                    <Text style={[styles.itemsHeaderText, styles.colDesc]}>Description</Text>
                    <Text style={[styles.itemsHeaderText, styles.colQty]}>Qty</Text>
                    <Text style={[styles.itemsHeaderText, styles.colPrice]}>Price</Text>
                    <Text style={[styles.itemsHeaderText, styles.colAmount]}>Amount</Text>
                </View>

                {/* Items */}
                {items.map((item, index) => (
                    <View key={index} style={styles.itemRow}>
                        <View style={styles.colDesc}>
                            <Text style={styles.itemName}>{item.item_name}</Text>
                            {item.description && (
                                <Text style={styles.itemDescription}>{item.description}</Text>
                            )}
                        </View>
                        <Text style={styles.colQty}>{item.qty}</Text>
                        <Text style={styles.colPrice}>{Number(item.price).toFixed(2)}</Text>
                        <Text style={styles.colAmount}>
                            {(Number(item.qty) * Number(item.price)).toFixed(2)}
                        </Text>
                    </View>
                ))}

                {/* Footer */}
                <View style={styles.footer}>
                    <View style={styles.notesSection}>
                        {invoice.notes && (
                            <View style={{ marginBottom: 15 }}>
                                <Text style={styles.label}>Note</Text>
                                <Text style={styles.notesText}>{invoice.notes}</Text>
                            </View>
                        )}
                        {invoice.bank_account_info && (
                            <View>
                                <Text style={styles.label}>Bank Details</Text>
                                <Text style={styles.notesText}>{invoice.bank_account_info}</Text>
                            </View>
                        )}
                    </View>
                    <View style={styles.totalsSection}>
                        <View style={styles.totalRow}>
                            <Text style={styles.totalLabel}>Subtotal</Text>
                            <Text style={styles.totalValue}>
                                {invoice.currency} {subTotal.toFixed(2)}
                            </Text>
                        </View>
                        <View style={styles.grandTotal}>
                            <Text style={styles.grandTotalLabel}>Total Amount</Text>
                            <Text style={styles.grandTotalValue}>
                                {invoice.currency} {total.toFixed(2)}
                            </Text>
                        </View>
                    </View>
                </View>
            </Page>
        </Document>
    );
};

export const generateInvoicePdf = async (invoice: Invoice, userPreference: InvoicePdfProps['userPreference']) => {
    const blob = await pdf(<InvoicePdfDocument invoice={invoice} userPreference={userPreference} />).toBlob();
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `Invoice-${invoice.id}.pdf`;
    link.click();
    URL.revokeObjectURL(url);
};

export default InvoicePdfDocument;
