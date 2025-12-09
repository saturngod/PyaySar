import { useForm, Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';
import { FormEventHandler } from 'react';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Transition } from '@headlessui/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Company Preference',
        href: '/settings/preference',
    },
];

interface UserPreference {
    company_name: string;
    company_email: string;
    company_address: string;
    company_logo: string | null;
}

export default function Preference({ preference }: { preference: UserPreference }) {
    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        company_name: preference?.company_name || '',
        company_email: preference?.company_email || '',
        company_address: preference?.company_address || '',
        company_logo: null as File | null,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/settings/preference', {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company Preference" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Company Details"
                        description="Update your company information and logo for invoices"
                    />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="company_name">Company Name</Label>
                            <Input
                                id="company_name"
                                className="mt-1 block w-full"
                                value={data.company_name}
                                onChange={(e) => setData('company_name', e.target.value)}
                                required
                                autoComplete="organization"
                                placeholder="My Company Inc."
                            />
                            <InputError className="mt-2" message={errors.company_name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="company_email">Company Email</Label>
                            <Input
                                id="company_email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.company_email}
                                onChange={(e) => setData('company_email', e.target.value)}
                                autoComplete="email"
                                placeholder="billing@company.com"
                            />
                            <InputError className="mt-2" message={errors.company_email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="company_address">Company Address</Label>
                            <Textarea
                                id="company_address"
                                className="mt-1 block w-full"
                                value={data.company_address}
                                onChange={(e) => setData('company_address', e.target.value)}
                                placeholder="123 Business St, City, Country"
                            />
                            <InputError className="mt-2" message={errors.company_address} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="company_logo">Company Logo</Label>
                            {preference?.company_logo && (
                                <div className="mb-2">
                                    <img
                                        src={`/storage/${preference.company_logo}`}
                                        alt="Current Logo"
                                        className="h-16 w-16 object-cover rounded-full border"
                                    />
                                </div>
                            )}
                            <Input
                                id="company_logo"
                                type="file"
                                accept="image/*"
                                className="mt-1 block w-full cursor-pointer"
                                onChange={(e) => setData('company_logo', e.target.files ? e.target.files[0] : null)}
                            />
                            <p className="text-xs text-muted-foreground">Recommended: Square image, max 1MB.</p>
                            <InputError className="mt-2" message={errors.company_logo} />
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
