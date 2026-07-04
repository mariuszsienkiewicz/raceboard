import { Pagination } from "@heroui/react";

interface SearchPaginationProps {
    size?: "sm" | "md" | "lg";
    currentPage: number;
    totalPages: number;
    onPageChange: (page: number) => void;
}

export default function SearchPagination({
    size = "sm",
    currentPage,
    totalPages,
    onPageChange,
}: SearchPaginationProps) {
    const getPageNumbers = () => {
        const pages: (number | "ellipsis")[] = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) {
                pages.push(i);
            }
        } else {
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
        }
        return pages;
    };

    return (
        <div className="flex flex-col gap-2">
            <Pagination className="justify-center" size={size}>
                <Pagination.Content>
                    <Pagination.Item>
                        <Pagination.Previous
                            isDisabled={currentPage === 1}
                            onPress={() => onPageChange(currentPage - 1)}
                        >
                            <Pagination.PreviousIcon />
                            <span>Previous</span>
                        </Pagination.Previous>
                    </Pagination.Item>
                    {getPageNumbers().map((pageNumber, i) =>
                        pageNumber === "ellipsis" ? (
                            <Pagination.Item key={`ellipsis-${i}`}>
                                <Pagination.Ellipsis />
                            </Pagination.Item>
                        ) : (
                            <Pagination.Item key={pageNumber}>
                                <Pagination.Link
                                    isActive={pageNumber === currentPage}
                                    onPress={() => onPageChange(pageNumber)}
                                >
                                    {pageNumber}
                                </Pagination.Link>
                            </Pagination.Item>
                        ),
                    )}
                    <Pagination.Item>
                        <Pagination.Next
                            isDisabled={currentPage === totalPages}
                            onPress={() => onPageChange(currentPage + 1)}
                        >
                            <span>Next</span>
                            <Pagination.NextIcon />
                        </Pagination.Next>
                    </Pagination.Item>
                </Pagination.Content>
            </Pagination>
        </div>
    );
}
