import { GlobeAltIcon, ListBulletIcon } from "@heroicons/react/24/outline";
import { Button, ButtonGroup } from "@heroui/react";

interface ResultViewModeSwitcherProps {
    onViewModeChange?: (viewMode: "list" | "map") => void;
}

export default function ResultViewModeSwitcher({ onViewModeChange }: ResultViewModeSwitcherProps) {
    return (
        <ButtonGroup variant="tertiary">
            <Button isIconOnly onClick={() => onViewModeChange?.("list")}>
                <ListBulletIcon />
            </Button>
            <Button isIconOnly onClick={() => onViewModeChange?.("map")}>
                <ButtonGroup.Separator />
                <GlobeAltIcon />
            </Button>
        </ButtonGroup>

    )
}