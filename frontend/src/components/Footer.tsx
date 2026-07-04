import { Link } from "react-router-dom";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { cn } from "@/lib/utils";

const LINKS = {
    Product: [
        { label: "Search races", to: "/" },
        { label: "My watchlist", to: "#" },
        { label: "Reviews", to: "#" },
    ],
    Resources: [
        { label: "API docs", to: "#" },
        { label: "About", to: "#" },
        { label: "Blog", to: "#" },
    ],
    Legal: [
        { label: "Privacy policy", to: "#" },
        { label: "Terms of use", to: "#" },
        { label: "Cookie policy", to: "#" },
    ],
} as const;

const footerLinkClassName =
    "text-sm text-muted-foreground transition-colors hover:text-foreground";

export default function Footer() {
    const year = new Date().getFullYear();

    return (
        <footer className="mt-auto border-t border-border bg-muted/30">
            <div className="mx-auto max-w-5xl px-6 py-12">
                <div className="grid grid-cols-2 gap-8 sm:grid-cols-4">
                    <div className="col-span-2 flex flex-col gap-4 sm:col-span-1">
                        <Link
                            to="/"
                            className="text-lg font-bold tracking-tight text-foreground transition-opacity hover:opacity-80"
                        >
                            Raceboard
                        </Link>
                        <p className="max-w-[200px] text-sm leading-relaxed text-muted-foreground">
                            Poland&apos;s running calendar — find your next race.
                        </p>
                        <Badge
                            variant="outline"
                            className="h-6 w-fit gap-2 border-border/80 bg-background/80 px-3 text-xs font-medium text-muted-foreground"
                        >
                            <span className="size-2 animate-pulse rounded-full bg-emerald-500" />
                            Live data
                        </Badge>
                    </div>

                    {Object.entries(LINKS).map(([section, links]) => (
                        <div key={section} className="flex flex-col gap-3">
                            <span className="text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                                {section}
                            </span>
                            <ul className="flex flex-col gap-2.5">
                                {links.map(({ label, to }) => (
                                    <li key={label}>
                                        <Link to={to} className={footerLinkClassName}>
                                            {label}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>

                <Separator className="my-8" />

                <div className="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                    <p className={cn("text-xs text-muted-foreground")}>© {year} Raceboard.</p>
                </div>
            </div>
        </footer>
    );
}
