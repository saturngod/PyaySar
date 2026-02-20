import { useForm, Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';
import { FormEventHandler } from 'react';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Transition } from '@headlessui/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'PDF Settings',
        href: '/settings/pdf',
    },
];

interface UserPreference {
    pdf_footer_message: string | null;
    pdf_paper_size: string;
    pdf_font: string | null;
    pdf_primary_color: string | null;
}

interface Props {
    preference: UserPreference;
    fonts: string[];
}

export default function PdfSettings({ preference, fonts }: Props) {
    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        pdf_footer_message: preference?.pdf_footer_message || '',
        pdf_paper_size: preference?.pdf_paper_size || 'a4',
        pdf_font: preference?.pdf_font || '',
        pdf_primary_color: preference?.pdf_primary_color || '#0f172a',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/settings/pdf', {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="PDF Settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="PDF Invoice Customization"
                        description="Personalize how your invoices look when generated as PDF"
                    />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="pdf_footer_message">Footer Message</Label>
                            <Textarea
                                id="pdf_footer_message"
                                className="mt-1 block w-full"
                                value={data.pdf_footer_message ?? ''}
                                onChange={(e) => setData('pdf_footer_message', e.target.value)}
                                placeholder="e.g. Thank you for your business!"
                                rows={3}
                            />
                            <p className="text-xs text-muted-foreground">This message will appear at the bottom of every invoice.</p>
                            <InputError className="mt-2" message={errors.pdf_footer_message} />
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="pdf_paper_size">Paper Size</Label>
                                <Select 
                                    value={data.pdf_paper_size} 
                                    onValueChange={(value) => setData('pdf_paper_size', value)}
                                >
                                    <SelectTrigger id="pdf_paper_size" className="mt-1">
                                        <SelectValue placeholder="Select paper size" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="a4">A4</SelectItem>
                                        <SelectItem value="letter">Letter</SelectItem>
                                        <SelectItem value="legal">Legal</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError className="mt-2" message={errors.pdf_paper_size} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="pdf_font">Custom Font (Optional)</Label>
                                <Select 
                                    value={data.pdf_font ?? 'default'} 
                                    onValueChange={(value) => setData('pdf_font', value === 'default' ? '' : value)}
                                >
                                    <SelectTrigger id="pdf_font" className="mt-1">
                                        <SelectValue placeholder="Choose a font" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="default">System Default</SelectItem>
                                        {fonts.map((font) => (
                                            <SelectItem key={font} value={font}>
                                                {font.replace('.ttf', '')}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <p className="text-xs text-muted-foreground">Select a custom font for the PDF content.</p>
                                <InputError className="mt-2" message={errors.pdf_font} />
                            </div>
                        </div>

                        <div className="grid gap-2 w-full md:w-1/2">
                            <Label htmlFor="pdf_primary_color">Primary Theme Color</Label>
                            <div className="flex items-center gap-3">
                                <Input
                                    id="pdf_primary_color"
                                    type="color"
                                    className="h-10 w-20 p-1 cursor-pointer"
                                    value={data.pdf_primary_color ?? '#0f172a'}
                                    onChange={(e) => setData('pdf_primary_color', e.target.value)}
                                />
                                <Input
                                    type="text"
                                    className="flex-1"
                                    value={data.pdf_primary_color ?? '#0f172a'}
                                    onChange={(e) => setData('pdf_primary_color', e.target.value)}
                                    placeholder="#000000"
                                />
                            </div>
                            <p className="text-xs text-muted-foreground">Used for headers, highlights, and the PAID stamp.</p>
                            <InputError className="mt-2" message={errors.pdf_primary_color} />
                        </div>

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Save PDF Settings</Button>

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
