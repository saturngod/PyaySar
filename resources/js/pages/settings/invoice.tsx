import { useForm, Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';
import { FormEventHandler } from 'react';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Transition } from '@headlessui/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Invoice Settings',
        href: '/settings/invoice',
    },
];

interface UserPreference {
    default_note: string | null;
    default_bank_account_info: string | null;
}

export default function InvoiceSettings({ preference }: { preference: UserPreference }) {
    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        default_note: preference?.default_note || '',
        default_bank_account_info: preference?.default_bank_account_info || '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/settings/invoice', {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Invoice Settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Invoice Defaults"
                        description="Default note and bank details prefilled when creating a new invoice"
                    />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="default_note">Default Note</Label>
                            <Textarea
                                id="default_note"
                                className="mt-1 block w-full"
                                value={data.default_note}
                                onChange={(e) => setData('default_note', e.target.value)}
                                placeholder="Thank you for your business..."
                            />
                            <InputError className="mt-2" message={errors.default_note} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="default_bank_account_info">Default Bank Details</Label>
                            <Textarea
                                id="default_bank_account_info"
                                className="mt-1 block w-full"
                                value={data.default_bank_account_info}
                                onChange={(e) => setData('default_bank_account_info', e.target.value)}
                                placeholder={'Bank Name\nAccount Number\nSwift Code'}
                            />
                            <InputError className="mt-2" message={errors.default_bank_account_info} />
                        </div>

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Save Changes</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Saved</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
