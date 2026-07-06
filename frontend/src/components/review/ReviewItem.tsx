import type { Review } from "@/types/review";
import { Star } from "lucide-react";
import { cn } from "@/lib/utils";
import { Avatar, AvatarFallback } from "../ui/avatar";
import { Badge } from "../ui/badge";
import { Skeleton } from "../ui/skeleton";

interface ReviewItemProps {
    review: Review;
    isUserReview: boolean;
}

function StarRating({ rating }: { rating: number }) {
    return (
        <div className="flex items-center gap-0.5">
            {Array.from({ length: 5 }).map((_, index) => (
                <Star
                    key={index}
                    className={cn(
                        "size-4",
                        index < rating
                            ? "fill-primary text-primary"
                            : "text-muted-foreground/40",
                    )}
                />
            ))}
        </div>
    );
}

export function ReviewItemSkeleton() {
    return (
        <article className="flex flex-col gap-3 rounded-2xl border border-border bg-card p-5 shadow-sm">
            <div className="flex items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                    <Skeleton className="size-8 rounded-full" />
                    <div className="flex flex-col gap-1.5">
                        <Skeleton className="h-4 w-24" />
                        <Skeleton className="h-3 w-16" />
                    </div>
                </div>
                <div className="flex gap-0.5">
                    {Array.from({ length: 5 }).map((_, index) => (
                        <Skeleton key={index} className="size-4 rounded-sm" />
                    ))}
                </div>
            </div>
            <Skeleton className="h-4 w-full" />
            <Skeleton className="h-4 w-4/5" />
        </article>
    );
}

export default function ReviewItem({ review, isUserReview }: ReviewItemProps) {
    return (
        <article
            className={cn(
                "flex flex-col gap-3 rounded-2xl border bg-card p-5 text-card-foreground shadow-sm",
                isUserReview
                    ? "border-primary/25 bg-primary/[0.03]"
                    : "border-border",
            )}
        >
            <div className="flex items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                    <Avatar size="sm">
                        <AvatarFallback>{review.displayName.charAt(0)}</AvatarFallback>
                    </Avatar>
                    <div>
                        <div className="flex items-center gap-2">
                            <p className="text-sm font-medium text-foreground">{review.displayName}</p>
                            {isUserReview && (
                                <Badge variant="secondary" className="h-5 px-1.5 text-[10px] font-medium">
                                    You
                                </Badge>
                            )}
                        </div>
                        <p className="text-xs text-muted-foreground">{review.createdAt}</p>
                    </div>
                </div>
                <StarRating rating={review.rating} />
            </div>
            <p className="text-sm leading-relaxed text-muted-foreground">{review.comment}</p>
        </article>
    );
}