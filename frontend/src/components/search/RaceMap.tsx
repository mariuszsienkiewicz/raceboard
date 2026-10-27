import { useEffect, useRef, useState } from "react";
import { MapContainer, Popup, CircleMarker, useMap, useMapEvents } from "react-leaflet";
import MapTiles from "@/components/map/MapTiles";
import MarkerClusterGroup from "react-leaflet-cluster";
import type { MapSearchPoint } from "@/types/search";
import SearchReturnLink from "./SearchReturnLink";
import "leaflet/dist/leaflet.css";

/** Matches `--primary` from shadcn theme (oklch(0.205 0 0)). */
const MARKER_FILL = "oklch(0.205 0 0)";
const MARKER_STROKE = "oklch(1 0 0)";

export interface MapBounds {
    topLat: number;
    topLng: number;
    bottomLat: number;
    bottomLng: number;
}

interface RaceMapProps {
    points: MapSearchPoint[];
    fullHeight?: boolean;
    restoreBounds?: MapBounds | null;
    onBoundsChange?: (bounds: MapBounds) => void;
}

function BoundsWatcher({
    onBoundsChange,
    suppressInitial,
}: {
    onBoundsChange: (bounds: MapBounds) => void;
    suppressInitial?: boolean;
}) {
    const onBoundsChangeRef = useRef(onBoundsChange);

    useEffect(() => {
        onBoundsChangeRef.current = onBoundsChange;
    }, [onBoundsChange]);

    const map = useMapEvents({
        moveend: () => {
            const b = map.getBounds();
            onBoundsChangeRef.current({
                topLat: b.getNorth(),
                topLng: b.getEast(),
                bottomLat: b.getSouth(),
                bottomLng: b.getWest(),
            });
        },
    });

    useEffect(() => {
        if (suppressInitial) {
            return;
        }
        const b = map.getBounds();
        onBoundsChangeRef.current({
            topLat: b.getNorth(),
            topLng: b.getEast(),
            bottomLat: b.getSouth(),
            bottomLng: b.getWest(),
        });
    }, [map, suppressInitial]);

    return null;
}

function InitialBoundsFitter({ bounds }: { bounds: MapBounds }) {
    const map = useMap();
    const fittedRef = useRef(false);

    useEffect(() => {
        if (fittedRef.current) {
            return;
        }
        fittedRef.current = true;
        map.fitBounds([
            [bounds.bottomLat, bounds.bottomLng],
            [bounds.topLat, bounds.topLng],
        ]);
    }, [map, bounds]);

    return null;
}

export default function RaceMap({
    points,
    fullHeight = false,
    restoreBounds = null,
    onBoundsChange,
}: RaceMapProps) {
    const [boundsToRestore] = useState(restoreBounds);
    const pointsWithGeo = points.filter((p) => p._geo);

    return (
        <MapContainer
            center={[52.0, 19.0]}
            zoom={6}
            maxZoom={16}
            className={fullHeight ? "h-full w-full" : "w-full rounded-2xl border border-border"}
            style={fullHeight ? undefined : { height: "600px" }}
        >
            <MapTiles />
            {boundsToRestore && <InitialBoundsFitter bounds={boundsToRestore} />}
            {onBoundsChange && (
                <BoundsWatcher
                    onBoundsChange={onBoundsChange}
                    suppressInitial={boundsToRestore !== null}
                />
            )}
            <MarkerClusterGroup chunkedLoading maxClusterRadius={50}>
                {pointsWithGeo.map((point) => (
                    <CircleMarker
                        key={point.id}
                        center={[point._geo!.lat, point._geo!.lng]}
                        radius={8}
                        pathOptions={{
                            fillColor: MARKER_FILL,
                            fillOpacity: 0.9,
                            color: MARKER_STROKE,
                            weight: 2,
                        }}
                    >
                        <Popup>
                            <SearchReturnLink to={`/races/${point.id}`}>
                                <strong>{point.name}</strong>
                            </SearchReturnLink>
                            <br />
                            {point.city}
                        </Popup>
                    </CircleMarker>
                ))}
            </MarkerClusterGroup>
        </MapContainer>
    );
}
