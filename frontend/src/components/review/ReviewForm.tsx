import { useState } from "react";
import { Link } from "react-router-dom";
import { Star } from "lucide-react";
import { Button, buttonVariants } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { cn } from "@/lib/utils";
import { useAuth } from "@/context/useAuth";

const RATING_LABELS = ["Terrible", "Poor", "OK", "Good", "Excellent"] as const;

interface ReviewFormProps {
    onSubmit: (rating: number, comment: string) => Promise<void>;
}

function ReviewCard({ className, ...props }: React.ComponentProps<"div">) {
    return (
        <div
            className={cn(
                "rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-sm",
                className,
            )}
            {...props}
        />
    );
}

export default function ReviewForm({ onSubmit }: ReviewFormProps) {
    const { isAuthenticated } = useAuth();
    const [rating, setRating] = useState(0);
    const [hovered, setHovered] = useState(0);
    const [comment, setComment] = useState("");
    const [loading, setLoading] = useState(false);
    const [submitted, setSubmitted] = useState(false);

    const isValid = rating > 0 && comment.trim().length > 0;

    const handleSubmit = async (event: React.FormEvent) => {
        event.preventDefault();
        if (!isValid) {
            return;
        }

        setLoading(true);
        try {
            await onSubmit(rating, comment.trim());
            setSubmitted(true);
        } finally {
            setLoading(false);
        }
    };

    if (!isAuthenticated) {
        return (
            <ReviewCard className="flex flex-col items-center gap-4 p-8 text-center">
                <div className="flex size-12 items-center justify-center rounded-full bg-primary/10">
                    <Star className="size-6 fill-primary text-primary" />
                </div>
                <div className="flex flex-col gap-1">
                    <p className="text-sm font-semibold text-foreground">Have you run this race?</p>
                    <p className="text-sm leading-relaxed text-muted-foreground">
                        Log in to share your experience and help other runners decide.
                    </p>
                </div>
                <div className="flex items-center gap-3">
                    <Button size="sm" render={<Link to="/login" />} nativeButton={false}>
                        Log in
                    </Button>
                    <Link
                        to="/register"
                        className={cn(buttonVariants({ variant: "link", size: "sm" }), "px-0")}
                    >
                        Create account
                    </Link>
                </div>
            </ReviewCard>
        );
    }

    if (submitted) {
        return (
            <ReviewCard className="flex items-center gap-3">
                <div className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10">
                    <Star className="size-4 fill-primary text-primary" />
                </div>
                <div>
                    <p className="text-sm font-medium text-foreground">Review submitted!</p>
                    <p className="text-xs text-muted-foreground">Thank you for sharing your experience.</p>
                </div>
            </ReviewCard>
        );
    }

    return (
        <ReviewCard className="flex flex-col gap-4">
            <div className="flex flex-col gap-1">
                <p className="text-sm font-semibold text-foreground">Leave a review</p>
                <p className="text-xs text-muted-foreground">Share your experience with other runners.</p>
            </div>

            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                <div className="flex flex-col gap-1.5">
                    <Label className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                        Rating
                    </Label>
                    <div className="flex items-center gap-1">
                        {Array.from({ length: 5 }).map((_, index) => {
                            const value = index + 1;
                            const filled = value <= (hovered || rating);

                            return (
                                <button
                                    key={value}
                                    type="button"
                                    onClick={() => setRating(value)}
                                    onMouseEnter={() => setHovered(value)}
                                    onMouseLeave={() => setHovered(0)}
                                    aria-label={`Rate ${value} out of 5`}
                                    className="transition-transform hover:scale-110"
                                >
                                    <Star
                                        className={cn(
                                            "size-6",
                                            filled
                                                ? "fill-primary text-primary"
                                                : "text-muted-foreground/40 hover:text-foreground/60",
                                        )}
                                    />
                                </button>
                            );
                        })}
                        {rating > 0 && (
                            <span className="ml-2 text-xs text-muted-foreground">
                                {RATING_LABELS[rating - 1]}
                            </span>
                        )}
                    </div>
                </div>

                <div className="flex flex-col gap-1.5">
                    <Label
                        htmlFor="review-comment"
                        className="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        Comment
                    </Label>
                    <Textarea
                        id="review-comment"
                        rows={3}
                        value={comment}
                        onChange={(event) => setComment(event.target.value.slice(0, 500))}
                        placeholder="How was the organisation, the route, the atmosphere…"
                    />
                    <p className="text-right text-xs text-muted-foreground/60">{comment.length} / 500</p>
                </div>

                <div className="flex justify-end">
                    <Button type="submit" size="sm" disabled={!isValid || loading}>
                        {loading ? "Submitting…" : "Submit review"}
                    </Button>
                </div>
            </form>
        </ReviewCard>
    );
}
