import type { Review } from "@/types/review";
import ReviewForm from "./ReviewForm";
import { useCallback, useEffect, useState } from "react";
import { apiFetch } from "@/api/client";
import ReviewList from "./ReviewList";
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from "../ui/pagination";
import ReviewItem, { ReviewItemSkeleton } from "./ReviewItem";
import { Star } from "lucide-react";
import { cn } from "@/lib/utils";
import { Skeleton } from "../ui/skeleton";
import { toast } from "sonner";

interface RaceReviewsProps {
    raceId: string;
    averageRating: number | null;
}

interface RaceReviewsApiResponse {
    reviews: Review[];
    userReview: Review | null;
    reviewCount: number;
    page: number;
    perPage: number;
    totalPages: number;
}

function ReviewStats({ averageRating, reviewCount }: { averageRating: number | null; reviewCount: number }) {
    if (reviewCount === 0) {
        return <span className="text-sm text-muted-foreground">No reviews yet</span>;
    }

    return (
        <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
            <Star className="size-4 fill-primary text-primary" />
            {averageRating ? (
                <>
                    <span className="font-medium text-foreground">{averageRating.toFixed(1)}</span>
                    <span aria-hidden="true">·</span>
                </>
            ) : null}
            <span>
                {reviewCount} {reviewCount === 1 ? "review" : "reviews"}
            </span>
        </div>
    );
}

function ReviewStatsSkeleton() {
    return (
        <div className="flex items-center gap-1.5">
            <Skeleton className="size-4 rounded-full" />
            <Skeleton className="h-4 w-6" />
            <Skeleton className="h-4 w-20" />
        </div>
    );
}

function ReviewListSkeleton({ count = 2 }: { count?: number }) {
    return (
        <div className="flex flex-col gap-4">
            {Array.from({ length: count }).map((_, index) => (
                <ReviewItemSkeleton key={index} />
            ))}
        </div>
    );
}

export default function RaceReviews({ raceId, averageRating }: RaceReviewsProps) {
    const [loading, setLoading] = useState(true);
    const [hasLoadedOnce, setHasLoadedOnce] = useState(false);

    const [reviews, setReviews] = useState<Review[]>([]);
    const [userReview, setUserReview] = useState<Review | null>(null);
    const [reviewCount, setReviewCount] = useState<number>(0);
    const [page, setPage] = useState<number>(1);
    const [totalPages, setTotalPages] = useState<number>(1);
    const [reloadToken, setReloadToken] = useState(0);
    const perPage = 10;

    const refreshReviews = useCallback(() => {
        setReloadToken((token) => token + 1);
    }, []);

    useEffect(() => {
        let cancelled = false;

        const params = new URLSearchParams();
        params.set("page", page.toString());
        params.set("perPage", perPage.toString());
        apiFetch(`/api/races/${raceId}/reviews?${params.toString()}`)
            .then((res) => res.json())
            .then((data: RaceReviewsApiResponse) => {
                if (!cancelled) {
                    setReviews(data.reviews);
                    setUserReview(data.userReview);
                    setReviewCount(data.reviewCount);
                    setTotalPages(data.totalPages);
                }
            })
            .catch((err) => {
                if (!cancelled) {
                    console.error("Failed to fetch reviews:", err);
                }
            })
            .finally(() => {
                if (!cancelled) {
                    setHasLoadedOnce(true);
                    setLoading(false);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [raceId, page, perPage, reloadToken]);

    const goToPage = (nextPage: number) => {
        setLoading(true);
        setPage(nextPage);
    };

    const handleAddReview = async (rating: number, comment: string): Promise<void> => {
        const res = await apiFetch(`/api/races/${raceId}/reviews`, {
            method: "POST",
            body: JSON.stringify({ rating, comment }),
        });

        if (!res.ok) {
            toast.error("Failed to submit review. Please try again.");
            throw new Error("Failed to submit review");
        }

        toast.success("Review submitted");
        setLoading(true);
        setPage(1);
        refreshReviews();
    };

    const handleDeleteReview = async (reviewId: string): Promise<void> => {
        const res = await apiFetch(`/api/reviews/${reviewId}`, {
            method: "DELETE",
        });

        if (!res.ok) {
            throw new Error("Failed to delete review");
        }

        setUserReview(null);
        setReviewCount((count) => Math.max(0, count - 1));
        setReviews((current) => current.filter((review) => review.id !== reviewId));
        setLoading(true);
        refreshReviews();
    };

    const communityReviews = userReview
        ? reviews.filter((review) => review.id !== userReview.id)
        : reviews;

    const showPagination = hasLoadedOnce && totalPages > 1;
    const showStatsSkeleton = loading && !hasLoadedOnce;

    return (
        <section className="flex flex-col gap-5">
            <div className="flex items-center justify-between gap-4">
                <h2 className="text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                    Reviews
                </h2>
                {showStatsSkeleton ? (
                    <ReviewStatsSkeleton />
                ) : (
                    <ReviewStats averageRating={averageRating} reviewCount={reviewCount} />
                )}
            </div>

            {hasLoadedOnce && !userReview && <ReviewForm onSubmit={handleAddReview} />}

            {hasLoadedOnce && userReview && (
                <ReviewItem
                    review={userReview}
                    isUserReview
                    onDelete={handleDeleteReview}
                />
            )}

            {loading ? (
                <ReviewListSkeleton count={hasLoadedOnce ? 1 : 2} />
            ) : (
                <ReviewList
                    reviews={communityReviews}
                    emptyMessage={
                        userReview
                            ? "No other reviews yet."
                            : undefined
                    }
                />
            )}

            {showPagination && (
                <Pagination className="mx-0 w-auto">
                    <PaginationContent>
                        <PaginationItem>
                            <PaginationPrevious
                                onClick={() => page > 1 && goToPage(page - 1)}
                                className={cn(page === 1 && "pointer-events-none opacity-50")}
                            />
                        </PaginationItem>
                        <PaginationItem>
                            <PaginationNext
                                onClick={() => page < totalPages && goToPage(page + 1)}
                                className={cn(
                                    (page === totalPages || totalPages === 0) && "pointer-events-none opacity-50",
                                )}
                            />
                        </PaginationItem>
                    </PaginationContent>
                </Pagination>
            )}
        </section>
    );
}
