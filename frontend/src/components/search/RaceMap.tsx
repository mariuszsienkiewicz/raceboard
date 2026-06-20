import { MapContainer, TileLayer, Popup, CircleMarker } from 'react-leaflet';
import { Link } from 'react-router-dom';
import type { Race } from '../../types/race';
import 'leaflet/dist/leaflet.css';
import MarkerClusterGroup from 'react-leaflet-cluster';

interface RaceMapProps {
    races: Race[];
}

export default function RaceMap({ races }: RaceMapProps) {
    const racesWithGeo = races.filter(r => r._geo);

    return (
        <MapContainer
            center={[52.0, 19.0]}
            zoom={6}
            style={{ height: '600px', width: '100%', borderRadius: '12px' }}
        >
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
                url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png"
            />
            <MarkerClusterGroup
                chunkedLoading
                maxClusterRadius={50}
            >
                {racesWithGeo.map(race => (
                    <CircleMarker
                        key={race.id}
                        center={[race._geo!.lat, race._geo!.lng]}
                        radius={8}
                        pathOptions={{
                            fillColor: '#006FEE',
                            fillOpacity: 0.9,
                            color: '#fff',
                            weight: 2,
                        }}
                    >
                        <Popup>
                            <Link to={`/races/${race.id}`}>
                                <strong>{race.name}</strong>
                            </Link>
                            <br />
                            {race.city}
                        </Popup>
                    </CircleMarker>
                ))}
            </MarkerClusterGroup>
        </MapContainer>
    );
}
