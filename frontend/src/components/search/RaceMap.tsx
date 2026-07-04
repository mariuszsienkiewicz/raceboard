import { useEffect, useRef } from "react";
import { MapContainer, TileLayer, Popup, CircleMarker, useMap, useMapEvents } from "react-leaflet";
import type { MapSearchPoint } from "../../types/search";
import "leaflet/dist/leaflet.css";
import MarkerClusterGroup from "react-leaflet-cluster";
import SearchReturnLink from "./SearchReturnLink";

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
    onBoundsChangeRef.current = onBoundsChange;

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
    const boundsToRestore = useRef(restoreBounds);
    const pointsWithGeo = points.filter((p) => p._geo);

    return (
        <MapContainer
            center={[52.0, 19.0]}
            zoom={6}
            className={fullHeight ? "h-full w-full" : "w-full rounded-xl"}
            style={fullHeight ? undefined : { height: "600px" }}
        >
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
                url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png"
            />
            {boundsToRestore.current && <InitialBoundsFitter bounds={boundsToRestore.current} />}
            {onBoundsChange && (
                <BoundsWatcher
                    onBoundsChange={onBoundsChange}
                    suppressInitial={!!boundsToRestore.current}
                />
            )}
            <MarkerClusterGroup chunkedLoading maxClusterRadius={50}>
                {pointsWithGeo.map((point) => (
                    <CircleMarker
                        key={point.id}
                        center={[point._geo!.lat, point._geo!.lng]}
                        radius={8}
                        pathOptions={{
                            fillColor: "#006FEE",
                            fillOpacity: 0.9,
                            color: "#fff",
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
