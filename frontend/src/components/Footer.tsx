import { Link } from "react-router-dom";

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
};

export default function Footer() {
    const year = new Date().getFullYear();

    return (
        <footer className="mt-auto border-t border-border bg-surface/50">
            <div className="mx-auto max-w-5xl px-6 py-12">
                <div className="grid grid-cols-2 gap-8 sm:grid-cols-4">
                    {/* Brand */}
                    <div className="col-span-2 flex flex-col gap-4 sm:col-span-1">
                        <Link to="/" className="text-lg font-bold text-foreground">
                            Raceboard
                        </Link>
                        <p className="text-sm text-muted leading-relaxed max-w-[200px]">
                            Poland's running calendar - find your next race.
                        </p>
                        <div className="flex items-center gap-2 rounded-full border border-border bg-background w-fit px-3 py-1">
                            <span className="size-2 rounded-full bg-emerald-400 animate-pulse" />
                            <span className="text-xs text-muted">Live data</span>
                        </div>
                    </div>

                    {/* Link columns */}
                    {Object.entries(LINKS).map(([section, links]) => (
                        <div key={section} className="flex flex-col gap-3">
                            <span className="text-xs font-semibold uppercase tracking-widest text-muted">
                                {section}
                            </span>
                            <ul className="flex flex-col gap-2.5">
                                {links.map(({ label, to }) => (
                                    <li key={label}>
                                        <Link
                                            to={to}
                                            className="text-sm text-muted hover:text-foreground transition-colors"
                                        >
                                            {label}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>

                <div className="mt-10 flex flex-col items-start justify-between gap-3 border-t border-border pt-6 sm:flex-row sm:items-center">
                    <p className="text-xs text-muted">
                        © {year} Raceboard.
                    </p>
                </div>
            </div>
        </footer>
    );
}
