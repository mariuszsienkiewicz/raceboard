import { Link, type LinkProps } from "react-router-dom";
import { saveSearchScrollPosition, useSearchState } from "../../hooks/useSearchState";

interface SearchReturnLinkProps extends Omit<LinkProps, "state"> {
    to: string;
}

export default function SearchReturnLink({ to, onClick, ...props }: SearchReturnLinkProps) {
    const { returnPath } = useSearchState();

    return (
        <Link
            {...props}
            to={to}
            state={{ from: returnPath }}
            onClick={(e) => {
                saveSearchScrollPosition();
                onClick?.(e);
            }}
        />
    );
}
