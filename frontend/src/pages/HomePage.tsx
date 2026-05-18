import Search from '../components/search/Search';

export default function HomePage() {
    return (
        <div className="flex flex-col gap-10">
            <div className="flex flex-col items-center gap-4 pt-10 pb-2 text-center">
                <span className="inline-flex items-center gap-1.5 rounded-full border border-border bg-surface px-3.5 py-1 text-xs font-medium text-muted tracking-wide uppercase">
                    🏃 Poland's running calendar
                </span>
                <h1 className="text-5xl font-bold tracking-tight text-foreground">
                    Find your next race
                </h1>
                <p className="max-w-lg text-base text-muted leading-relaxed">
                    Discover hundreds of running events across Poland - from local 5Ks to full marathons.
                </p>
            </div>
            <Search />
        </div>
    );
}
