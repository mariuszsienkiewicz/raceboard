import { Avatar, AvatarFallback, Surface } from "@heroui/react";
import { useEffect, useState } from "react";
import { StarIcon as StarSolidIcon } from "@heroicons/react/24/solid";
import {
    StarIcon,
} from "@heroicons/react/24/outline";
import type { Review } from "../../types/review";
import { apiFetch } from "../../api/client";

interface RaceReviewsProps {
    raceId: string;
}

export default function RaceReviews({ raceId }: RaceReviewsProps) {
    const [reviews, setReviews] = useState<Review[]>([]);

    const fetchReviews = async () => {
        apiFetch(`/api/races/${raceId}/reviews`)
            .then((res) => res.json())
            .then((data: Review[]) => setReviews(data))
            .catch((err) => console.error("Failed to fetch reviews:", err));
    }

    useEffect(() => {
        fetchReviews();
    }, [raceId]);

    function StarRating({ rating }: { rating: number }) {
        return (
            <div className="flex items-center gap-0.5">
                {Array.from({ length: 5 }).map((_, i) =>
                    i < rating
                        ? <StarSolidIcon key={i} className="size-4 text-amber-400" />
                        : <StarIcon key={i} className="size-4 text-muted" />
                )}
            </div>
        );
    }

    return (
        <>
            {reviews.length === 0 ? (
                <p className="text-sm text-muted">No reviews yet. Be the first to share your experience!</p>
            ) : (
                <div className="flex flex-col gap-4">
                    {reviews.map((review) => (
                        <Surface key={review.id} variant="default" className="flex flex-col gap-3 rounded-2xl p-5">
                            <div className="flex items-center justify-between gap-4">
                                <div className="flex items-center gap-3">
                                    <Avatar>
                                        {/* TODO - change it to first char of the username */}
                                        <AvatarFallback>{review.userId.charAt(0)}</AvatarFallback>
                                    </Avatar>
                                    <div>
                                        {/* TODO - change it to display the username */}
                                        <p className="text-sm font-medium text-foreground">{review.userId}</p>
                                        <p className="text-xs text-muted">{review.createdAt}</p>
                                    </div>
                                </div>
                                <StarRating rating={review.rating} />
                            </div>
                            <p className="text-sm text-muted leading-relaxed">{review.comment}</p>
                        </Surface>

                    ))}
                </div>
            )}
        </>
    )
}