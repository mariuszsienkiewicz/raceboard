import { useState } from "react";
import type { Review } from "@/types/review";
import { Star, Trash2 } from "lucide-react";
import { toast } from "sonner";
import { cn } from "@/lib/utils";
import { Avatar, AvatarFallback } from "../ui/avatar";
import { Badge } from "../ui/badge";
import { Button } from "../ui/button";
import { Skeleton } from "../ui/skeleton";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "../ui/dialog";

interface ReviewItemProps {
    review: Review;
    isUserReview: boolean;
    onDelete?: (reviewId: string) => Promise<void>;
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

export default function ReviewItem({ review, isUserReview, onDelete }: ReviewItemProps) {
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);

    const handleConfirmDelete = async () => {
        if (!onDelete) {
            return;
        }

        setDeleting(true);
        try {
            await onDelete(review.id);
            setConfirmOpen(false);
            toast.success("Review deleted");
        } catch {
            toast.error("Failed to delete review. Please try again.");
        } finally {
            setDeleting(false);
        }
    };

    return (
        <article
            className={cn(
                "flex flex-col gap-3 rounded-2xl border bg-card p-5 text-card-foreground shadow-sm",
                isUserReview
                    ? "border-primary/25 bg-primary/[0.03]"
                    : "border-border",
            )}
        >
            <div className="flex items-start justify-between gap-4">
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
                <div className="flex items-center gap-2">
                    <StarRating rating={review.rating} />
                    {isUserReview && onDelete && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            aria-label="Delete your review"
                            onClick={() => setConfirmOpen(true)}
                            className="text-muted-foreground hover:text-destructive"
                        >
                            <Trash2 />
                        </Button>
                    )}
                </div>
            </div>
            <p className="text-sm leading-relaxed text-muted-foreground">{review.comment}</p>

            <Dialog open={confirmOpen} onOpenChange={setConfirmOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete review?</DialogTitle>
                        <DialogDescription>
                            This will permanently remove your review for this race. You can leave a new one later.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={deleting}
                            onClick={() => setConfirmOpen(false)}
                        >
                            Cancel
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            disabled={deleting}
                            onClick={handleConfirmDelete}
                        >
                            {deleting ? "Deleting…" : "Delete"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </article>
    );
}
