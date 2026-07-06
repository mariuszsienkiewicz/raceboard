import type { Review } from "@/types/review";
import ReviewItem from '@/components/review/ReviewItem';

interface ReviewListProps {
    reviews: Review[];
    emptyMessage?: string;
}

export default function ReviewList({
    reviews,
    emptyMessage = "No reviews yet. Be the first to share your experience!",
}: ReviewListProps) {
    if (reviews.length === 0) {
        return (
            <p className="text-sm text-muted-foreground">
                {emptyMessage}
            </p>
        );
    }

    return (
        <div className="flex flex-col gap-4">
            {reviews.map((review) => (
                <ReviewItem key={review.id} review={review} isUserReview={false} />
            ))}
        </div>
    );
}
