import { Pagination } from "@heroui/react";
import { useState } from "react";

interface SearchPaginationProps {
    size?: "sm" | "md" | "lg";
    onPageChange?: (page: number) => void;
    totalPages?: number;
}

export default function SearchPagination({ size = "sm", onPageChange, totalPages = 1 }: SearchPaginationProps) {
    const [page, setPage] = useState(1);

    const handlePageChange = (newPage: number) => {
        setPage(newPage);
        onPageChange?.(newPage);
    }

    const getPageNumbers = () => {
        const pages: (number | "ellipsis")[] = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) {
                pages.push(i);
            }
        } else {
            pages.push(1);
            if (page > 3) {
                pages.push("ellipsis");
            }
            const start = Math.max(2, page - 1);
            const end = Math.min(totalPages - 1, page + 1);
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            if (page < totalPages - 2) {
                pages.push("ellipsis");
            }
            pages.push(totalPages);
        }
        return pages;
    };

    return (
        <div className="flex flex-col gap-2">
            <Pagination className="justify-center" size={size}>
                <Pagination.Content>
                    <Pagination.Item>
                        <Pagination.Previous isDisabled={page === 1} onPress={() => handlePageChange(page - 1)}>
                            <Pagination.PreviousIcon />
                            <span>Previous</span>
                        </Pagination.Previous>
                    </Pagination.Item>
                    {getPageNumbers().map((p, i) =>
                        p === "ellipsis" ? (
                            <Pagination.Item key={`ellipsis-${i}`}>
                                <Pagination.Ellipsis />
                            </Pagination.Item>
                        ) : (
                            <Pagination.Item key={p}>
                                <Pagination.Link isActive={p === page} onPress={() => handlePageChange(p)}>
                                    {p}
                                </Pagination.Link>
                            </Pagination.Item>
                        ),
                    )}
                    <Pagination.Item>
                        <Pagination.Next isDisabled={page === totalPages} onPress={() => handlePageChange(page + 1)}>
                            <span>Next</span>
                            <Pagination.NextIcon />
                        </Pagination.Next>
                    </Pagination.Item>
                </Pagination.Content>
            </Pagination>
        </div>
    );
}
