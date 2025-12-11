
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard, login } from '@/routes';
import { SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, CheckCircle2, Receipt, Users } from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome to Pyaysar" />

            <div className="min-h-screen bg-background text-foreground selection:bg-primary selection:text-primary-foreground">
                {/* Header */}
                <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="container mx-auto flex h-14 items-center justify-between px-4 md:px-6">
                        <div className="flex items-center gap-2 font-bold text-xl">
                            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-primary-foreground">
                                <AppLogoIcon className="size-5" />
                            </div>
                            <span>Pyaysar</span>
                        </div>
                        <nav className="flex items-center gap-4">
                            {auth.user ? (
                                <Link href={dashboard()}>
                                    <Button>Dashboard</Button>
                                </Link>
                            ) : (
                                <Link href={login()}>
                                    <Button variant="default">Log in</Button>
                                </Link>
                            )}
                        </nav>
                    </div>
                </header>

                <main className="flex-1">
                    {/* Hero Section */}
                    <section className="container mx-auto grid items-center gap-6 px-4 py-12 md:px-6 lg:grid-cols-2 lg:gap-10 lg:py-24">
                        <div className="flex flex-col gap-4">
                            <h1 className="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl lg:text-6xl/none">
                                Manage Your Business with <span className="text-primary">Confidence</span>
                            </h1>
                            <p className="max-w-[600px] text-muted-foreground md:text-xl">
                                The all-in-one solution for tracking customers, managing invoices, and visualizing your business performance.
                            </p>
                            <div className="flex flex-col gap-2 min-[400px]:flex-row">
                                {auth.user ? (
                                    <Link href={dashboard()}>
                                        <Button size="lg" className="gap-2">
                                            Go to Dashboard <ArrowRight className="h-4 w-4" />
                                        </Button>
                                    </Link>
                                ) : (
                                    <Link href={login()}>
                                        <Button size="lg" className="gap-2">
                                            Get Started <ArrowRight className="h-4 w-4" />
                                        </Button>
                                    </Link>
                                )}
                            </div>
                            <div className="flex items-center gap-4 text-sm text-muted-foreground mt-4">
                                <div className="flex items-center gap-1">
                                    <CheckCircle2 className="h-4 w-4 text-green-500" /> Free Updates
                                </div>
                                <div className="flex items-center gap-1">
                                    <CheckCircle2 className="h-4 w-4 text-green-500" /> Secure Data
                                </div>
                                <div className="flex items-center gap-1">
                                    <CheckCircle2 className="h-4 w-4 text-green-500" /> Premium Support
                                </div>
                            </div>
                        </div>
                        <div className="flex items-center justify-center">
                            <div className="relative rounded-xl border bg-background p-2 shadow-2xl">
                                <img
                                    alt="Dashboard Preview"
                                    className="rounded-lg object-cover w-full h-auto"
                                    src="https://cldup.com/BDJCpIiC2O.png"
                                />
                            </div>
                        </div>
                    </section>

                    {/* Features Section */}
                    <section className="w-full py-12 md:py-24 lg:py-32 bg-muted/50">
                        <div className="container mx-auto px-4 md:px-6">
                            <div className="flex flex-col items-center justify-center space-y-4 text-center">
                                <div className="inline-block rounded-lg bg-muted px-3 py-1 text-sm font-medium text-primary">
                                    Features
                                </div>
                                <h2 className="text-3xl font-bold tracking-tighter sm:text-5xl">
                                    Everything you need to run your business
                                </h2>
                                <p className="max-w-[900px] text-muted-foreground md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">
                                    Streamline your operations with our powerful suite of tools designed for efficiency and growth.
                                </p>
                            </div>

                            <div className="mx-auto grid max-w-5xl items-center gap-6 py-12 lg:grid-cols-2 lg:gap-12">
                                <div className="flex flex-col justify-center space-y-4">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                        <Users className="h-6 w-6" />
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-2xl font-bold">Comprehensive Customer Management</h3>
                                        <p className="text-muted-foreground">
                                            Keep track of all your customers in one place. View detailed profiles, transaction history, and manage relationships effortlessly.
                                        </p>
                                    </div>
                                </div>
                                <div className="mx-auto w-full max-w-[500px] lg:max-w-none">
                                    <div className="relative rounded-xl border bg-background p-2 shadow-xl">
                                        <img
                                            alt="Customer List"
                                            className="rounded-lg object-cover w-full h-auto"
                                            src="https://cldup.com/ydcuzBhW9O.png"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="mx-auto grid max-w-5xl items-center gap-6 py-12 lg:grid-cols-2 lg:gap-12">
                                <div className="lg:order-last flex flex-col justify-center space-y-4">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                        <Receipt className="h-6 w-6" />
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-2xl font-bold">Powerful Invoicing</h3>
                                        <p className="text-muted-foreground">
                                            Create, customize, and manage invoices with ease. Track payments and ensure you get paid on time with our intuitive invoice manager.
                                        </p>
                                    </div>
                                </div>
                                <div className="mx-auto w-full max-w-[500px] lg:max-w-none lg:order-first">
                                    <div className="space-y-4">
                                        <div className="relative rounded-xl border bg-background p-2 shadow-xl">
                                            <img
                                                alt="Invoice List"
                                                className="rounded-lg object-cover w-full h-auto"
                                                src="https://cldup.com/8JQN7V-nMB.png"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* Screenshot Showcase */}
                    <section className="w-full py-12 md:py-24 lg:py-32">
                        <div className="container mx-auto px-4 md:px-6">
                            <div className="mb-12 text-center">
                                <h2 className="text-3xl font-bold tracking-tighter sm:text-4xl">
                                    Designed for Efficiency
                                </h2>
                                <p className="mt-4 text-muted-foreground md:text-lg">
                                    Take a closer look at our intuitive interface.
                                </p>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <Card className="overflow-hidden border-2">
                                    <CardHeader className="bg-muted/50 pb-2">
                                        <CardTitle>Create Invoices</CardTitle>
                                        <CardDescription>Simple yet powerful invoice creation</CardDescription>
                                    </CardHeader>
                                    <CardContent className="p-0">
                                        <img
                                            src="https://cldup.com/6a3vcvrwdO.png"
                                            alt="Create Invoice"
                                            className="w-full h-auto object-cover"
                                        />
                                    </CardContent>
                                </Card>
                                <Card className="overflow-hidden border-2">
                                    <CardHeader className="bg-muted/50 pb-2">
                                        <CardTitle>View Details</CardTitle>
                                        <CardDescription>Clear payment statuses and details</CardDescription>
                                    </CardHeader>
                                    <CardContent className="p-0">
                                        <img
                                            src="https://cldup.com/OoHSYDZdbi.png"
                                            alt="View Invoice"
                                            className="w-full h-auto object-cover"
                                        />
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </section>
                </main>

                <footer className="border-t py-6 md:py-0">
                    <div className="container mx-auto flex flex-col items-center justify-between gap-4 px-4 md:h-24 md:flex-row md:px-6">
                        <p className="text-center text-sm leading-loose text-muted-foreground md:text-left">
                            © {new Date().getFullYear()} Pyaysar. All rights reserved.
                        </p>
                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                            <Link href="#" className="hover:underline">
                                Terms
                            </Link>
                            <Link href="#" className="hover:underline">
                                Privacy
                            </Link>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
