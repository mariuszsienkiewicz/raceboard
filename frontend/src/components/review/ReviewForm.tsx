import { Button, Surface } from "@heroui/react";
import { useState } from "react";
import { Link } from "react-router-dom";
import { StarIcon, StarIcon as StarSolidIcon } from "@heroicons/react/24/solid";
import { useAuth } from "../../context/useAuth";

export default function ReviewForm({ onSubmit }: { onSubmit: (rating: number, comment: string) => Promise<void> }) {
    const { isAuthenticated } = useAuth();
    if (!isAuthenticated) {
        return (
            <Surface variant="default" className="flex flex-col items-center gap-4 rounded-2xl p-8 text-center">
                <div className="flex size-12 items-center justify-center rounded-full bg-primary/10">
                    <StarSolidIcon className="size-6 text-primary" />
                </div>
                <div className="flex flex-col gap-1">
                    <p className="text-sm font-semibold text-foreground">Have you run this race?</p>
                    <p className="text-sm text-muted leading-relaxed">
                        Log in to share your experience and help other runners decide.
                    </p>
                </div>
                <div className="flex items-center gap-3">
                    <Link to="/login">
                        <Button size="sm" className="rounded-xl font-medium px-5">
                            Log in
                        </Button>
                    </Link>
                    <Link
                        to="/register"
                        className="text-sm font-medium text-muted hover:text-foreground transition-colors"
                    >
                        Create account
                    </Link>
                </div>
            </Surface>
        );
    }

    const [rating, setRating] = useState(0);
    const [hovered, setHovered] = useState(0);
    const [comment, setComment] = useState("");
    const [loading, setLoading] = useState(false);
    const [submitted, setSubmitted] = useState(false);

    const isValid = rating > 0 && comment.trim().length > 0;

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!isValid) return;
        setLoading(true);
        try {
            await onSubmit(rating, comment.trim());
            setSubmitted(true);
        } finally {
            setLoading(false);
        }
    };

    if (submitted) {
        return (
            <Surface variant="default" className="flex items-center gap-3 rounded-2xl p-5">
                <div className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10">
                    <StarSolidIcon className="size-4 text-primary" />
                </div>
                <div>
                    <p className="text-sm font-medium text-foreground">Review submitted!</p>
                    <p className="text-xs text-muted">Thank you for sharing your experience.</p>
                </div>
            </Surface>
        );
    }

    return (
        <Surface variant="default" className="flex flex-col gap-4 rounded-2xl p-5">
            <div className="flex flex-col gap-1">
                <p className="text-sm font-semibold text-foreground">Leave a review</p>
                <p className="text-xs text-muted">Share your experience with other runners.</p>
            </div>

            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                {/* Star picker */}
                <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-semibold uppercase tracking-wide text-muted">Rating</label>
                    <div className="flex items-center gap-1">
                        {Array.from({ length: 5 }).map((_, i) => {
                            const value = i + 1;
                            const filled = value <= (hovered || rating);
                            return (
                                <button
                                    key={i}
                                    type="button"
                                    onClick={() => setRating(value)}
                                    onMouseEnter={() => setHovered(value)}
                                    onMouseLeave={() => setHovered(0)}
                                    aria-label={`Rate ${value} out of 5`}
                                    className="transition-transform hover:scale-110"
                                >
                                    {filled
                                        ? <StarSolidIcon className="size-6 text-amber-400" />
                                        : <StarIcon className="size-6 text-muted/40 hover:text-amber-300" />
                                    }
                                </button>
                            );
                        })}
                        {rating > 0 && (
                            <span className="ml-2 text-xs text-muted">
                                {["Terrible", "Poor", "OK", "Good", "Excellent"][rating - 1]}
                            </span>
                        )}
                    </div>
                </div>

                {/* Comment */}
                <div className="flex flex-col gap-1.5">
                    <label htmlFor="review-comment" className="text-xs font-semibold uppercase tracking-wide text-muted">
                        Comment
                    </label>
                    <textarea
                        id="review-comment"
                        rows={3}
                        value={comment}
                        onChange={(e) => setComment(e.target.value)}
                        placeholder="How was the organisation, the route, the atmosphere…"
                        className="w-full resize-none rounded-xl border border-border bg-surface px-4 py-3 text-sm text-foreground placeholder:text-muted/60 outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                    <p className="text-right text-xs text-muted/60">{comment.length} / 500</p>
                </div>

                <div className="flex justify-end">
                    <Button
                        type="submit"
                        size="sm"
                        isDisabled={!isValid || loading}
                        className="rounded-xl font-medium px-6"
                    >
                        {loading ? "Submitting…" : "Submit review"}
                    </Button>
                </div>
            </form>
        </Surface>
    );
}