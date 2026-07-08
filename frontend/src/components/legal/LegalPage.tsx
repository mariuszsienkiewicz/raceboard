import type { ComponentType, ReactNode } from "react";
import PageSeo from "@/components/PageSeo";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";

export interface LegalSection {
    id: string;
    title: string;
    content: ReactNode;
}

interface LegalPageProps {
    icon: ComponentType<{ className?: string; strokeWidth?: number }>;
    badge: string;
    title: string;
    seoDescription: string;
    intro: string;
    lastUpdated: string;
    sections: LegalSection[];
}

export default function LegalPage({
    icon: Icon,
    badge,
    title,
    seoDescription,
    intro,
    lastUpdated,
    sections,
}: LegalPageProps) {
    return (
        <div className="flex flex-col gap-10 py-4">
            <PageSeo title={title} description={seoDescription} />
            <header className="relative overflow-hidden rounded-4xl border border-border bg-card p-6 ring-1 ring-foreground/5 sm:p-8">
                <div
                    aria-hidden
                    className="pointer-events-none absolute -top-16 -right-10 size-56 rounded-full bg-primary/5 blur-3xl"
                />
                <div className="relative flex flex-col gap-4">
                    <Badge
                        variant="outline"
                        className="h-7 w-fit gap-1.5 border-border/80 bg-background/80 px-3.5 py-1 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        <Icon className="size-3.5" strokeWidth={1.75} />
                        {badge}
                    </Badge>
                    <h1 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        {title}
                    </h1>
                    <p className="max-w-2xl text-base leading-relaxed text-muted-foreground">{intro}</p>
                    <p className="text-xs text-muted-foreground">Last updated: {lastUpdated}</p>
                </div>
            </header>

            <div className="grid grid-cols-1 gap-10 lg:grid-cols-[220px_1fr]">
                <nav className="hidden lg:block">
                    <div className="sticky top-24 flex flex-col gap-3">
                        <span className="text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                            On this page
                        </span>
                        <ul className="flex flex-col gap-1.5">
                            {sections.map((section) => (
                                <li key={section.id}>
                                    <a
                                        href={`#${section.id}`}
                                        className="block rounded-lg py-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                    >
                                        {section.title}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>
                </nav>

                <div className="flex flex-col gap-10">
                    {sections.map((section, index) => (
                        <section key={section.id} id={section.id} className="scroll-mt-24">
                            <div className="flex flex-col gap-3">
                                <h2 className="flex items-baseline gap-2 text-xl font-semibold tracking-tight text-foreground">
                                    <span className="text-sm font-semibold text-primary tabular-nums">
                                        {String(index + 1).padStart(2, "0")}
                                    </span>
                                    {section.title}
                                </h2>
                                <div className="flex flex-col gap-3 text-sm leading-relaxed text-muted-foreground [&_a]:font-medium [&_a]:text-primary [&_a]:underline-offset-4 hover:[&_a]:underline [&_li]:leading-relaxed [&_strong]:font-semibold [&_strong]:text-foreground [&_ul]:flex [&_ul]:list-disc [&_ul]:flex-col [&_ul]:gap-2 [&_ul]:pl-5">
                                    {section.content}
                                </div>
                            </div>
                            {index < sections.length - 1 && <Separator className="mt-10" />}
                        </section>
                    ))}
                </div>
            </div>
        </div>
    );
}
