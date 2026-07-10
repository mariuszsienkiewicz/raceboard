package geocoder

import (
	"context"
	"raceboard/geocoder/internal/cache"
	"raceboard/geocoder/internal/domain"
	"sync"
	"testing"
)

type fakeGeocoder struct {
	mu        sync.Mutex
	callCount int // TODO: change it to atomic.Int64 instead of int and mutex.Lock/Unlock
	response  domain.Coordinates
}

func (g *fakeGeocoder) Geocode(city string) (domain.Coordinates, error) {
	g.mu.Lock()
	g.callCount++
	g.mu.Unlock()

	resp := g.response
	resp.City = city
	return resp, nil
}

func TestService_ReturnsResultForEachCity(t *testing.T) {
	fakeGeo := &fakeGeocoder{response: domain.Coordinates{Lat: 52.23, Lng: 21.01}}
	service := NewService(fakeGeo, cache.New(), 5)

	results := service.GeocodeMany(context.Background(), []string{"Warszawa", "Kraków", "Gdańsk"})

	if len(results) != 3 {
		t.Errorf("expected 3 results, got %d", len(results))
	}
}

func TestService_DeduplicatesViaCache(t *testing.T) {
	fakeGeo := &fakeGeocoder{response: domain.Coordinates{Lat: 52.23, Lng: 21.01}}
	service := NewService(fakeGeo, cache.New(), 5)

	service.GeocodeMany(context.Background(), []string{"Warszawa", "Warszawa", "Warszawa"})

	if fakeGeo.callCount != 1 {
		t.Errorf("expected geocoder called once for 3 identical cities, got %d", fakeGeo.callCount)
	}
}
