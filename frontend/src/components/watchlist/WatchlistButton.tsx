import { Heart, Loader2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { useAuth } from "@/context/useAuth";
import { useWatchlist } from "@/hooks/useWatchlist";

interface WatchlistButtonProps {
    raceId: string;
}

function AuthenticatedWatchlistButton({ raceId }: WatchlistButtonProps) {
    const { watched, toggle } = useWatchlist(raceId);

    if (watched === null) {
        return (
            <Button variant="outline" size="sm" disabled className="rounded-full">
                <Loader2 className="size-4 animate-spin" />
                Loading…
            </Button>
        );
    }

    return (
        <Button
            type="button"
            variant="outline"
            size="sm"
            aria-label={watched ? "Remove from watchlist" : "Add to watchlist"}
            onClick={toggle}
            className={cn(
                "rounded-full",
                watched
                    ? "border-red-200 bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600"
                    : "text-muted-foreground hover:border-red-200 hover:bg-red-50 hover:text-red-400",
            )}
        >
            <Heart className={cn("size-4", watched && "fill-current")} />
            <span className="hidden sm:inline">{watched ? "Watching" : "Watch"}</span>
        </Button>
    );
}

export default function WatchlistButton({ raceId }: WatchlistButtonProps) {
    const { isAuthenticated } = useAuth();

    if (!isAuthenticated) {
        return null;
    }

    return <AuthenticatedWatchlistButton raceId={raceId} />;
}
