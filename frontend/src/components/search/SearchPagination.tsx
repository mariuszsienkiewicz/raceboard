import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from "@/components/ui/pagination";
import { cn } from "@/lib/utils";

interface SearchPaginationProps {
    size?: "sm" | "md" | "lg";
    currentPage: number;
    totalPages: number;
    onPageChange: (page: number) => void;
}

function getPageNumbers(currentPage: number, totalPages: number): (number | "ellipsis")[] {
    const pages: (number | "ellipsis")[] = [];

    if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i);
        }
        return pages;
    }

    pages.push(1);

    if (currentPage > 3) {
        pages.push("ellipsis");
    }

    const start = Math.max(2, currentPage - 1);
    const end = Math.min(totalPages - 1, currentPage + 1);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    if (currentPage < totalPages - 2) {
        pages.push("ellipsis");
    }

    pages.push(totalPages);

    return pages;
}

function getLinkSize(size: SearchPaginationProps["size"]) {
    if (size === "lg") {
        return "icon-lg" as const;
    }
    if (size === "md") {
        return "icon" as const;
    }
    return "icon-sm" as const;
}

export default function SearchPagination({
    size = "sm",
    currentPage,
    totalPages,
    onPageChange,
}: SearchPaginationProps) {
    const linkSize = getLinkSize(size);
    const pageNumbers = getPageNumbers(currentPage, totalPages);

    function handlePageClick(page: number) {
        return (event: React.MouseEvent<HTMLAnchorElement>) => {
            event.preventDefault();
            onPageChange(page);
        };
    }

    return (
        <Pagination className="justify-center">
            <PaginationContent>
                <PaginationItem>
                    <PaginationPrevious
                        href="#"
                        text="Previous"
                        className={cn(currentPage === 1 && "pointer-events-none opacity-50")}
                        aria-disabled={currentPage === 1}
                        onClick={(event) => {
                            event.preventDefault();
                            if (currentPage > 1) {
                                onPageChange(currentPage - 1);
                            }
                        }}
                    />
                </PaginationItem>

                {pageNumbers.map((pageNumber, index) =>
                    pageNumber === "ellipsis" ? (
                        <PaginationItem key={`ellipsis-${index}`}>
                            <PaginationEllipsis />
                        </PaginationItem>
                    ) : (
                        <PaginationItem key={pageNumber}>
                            <PaginationLink
                                href="#"
                                size={linkSize}
                                isActive={pageNumber === currentPage}
                                onClick={handlePageClick(pageNumber)}
                            >
                                {pageNumber}
                            </PaginationLink>
                        </PaginationItem>
                    ),
                )}

                <PaginationItem>
                    <PaginationNext
                        href="#"
                        text="Next"
                        className={cn(currentPage === totalPages && "pointer-events-none opacity-50")}
                        aria-disabled={currentPage === totalPages}
                        onClick={(event) => {
                            event.preventDefault();
                            if (currentPage < totalPages) {
                                onPageChange(currentPage + 1);
                            }
                        }}
                    />
                </PaginationItem>
            </PaginationContent>
        </Pagination>
    );
}
