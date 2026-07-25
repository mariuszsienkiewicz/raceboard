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

type fakeMetrics struct {
	mu                  sync.Mutex
	cacheHitCount       int
	cacheMissCount      int
	nominatimErrorCount int
}

func (f *fakeMetrics) RecordCacheHit() {
	f.mu.Lock()
	f.cacheHitCount++
	f.mu.Unlock()
}

func (f *fakeMetrics) RecordCacheMiss() {
	f.mu.Lock()
	f.cacheMissCount++
	f.mu.Unlock()
}

func (f *fakeMetrics) RecordNominatimError() {
	f.mu.Lock()
	f.nominatimErrorCount++
	f.mu.Unlock()
}

func TestService_ReturnsResultForEachCity(t *testing.T) {
	fakeGeo := &fakeGeocoder{response: domain.Coordinates{Lat: 52.23, Lng: 21.01}}
	metrics := &fakeMetrics{}
	service := NewService(fakeGeo, cache.New(), metrics, 5)

	results := service.GeocodeMany(context.Background(), []string{"Warszawa", "Kraków", "Gdańsk"})

	if len(results) != 3 {
		t.Errorf("expected 3 results, got %d", len(results))
	}

	if fakeGeo.callCount != 3 {
		t.Errorf("expected geocoder called 3 times, got %d", fakeGeo.callCount)
	}
	if metrics.cacheMissCount != 3 {
		t.Errorf("expected 3 cache misses, got %d", metrics.cacheMissCount)
	}
	if metrics.cacheHitCount != 0 {
		t.Errorf("expected 0 cache hits, got %d", metrics.cacheHitCount)
	}
}

func TestService_DeduplicatesViaCache(t *testing.T) {
	fakeGeo := &fakeGeocoder{response: domain.Coordinates{Lat: 52.23, Lng: 21.01}}
	metrics := &fakeMetrics{}
	service := NewService(fakeGeo, cache.New(), metrics, 1)

	service.GeocodeMany(context.Background(), []string{"Warszawa", "Warszawa", "Warszawa"})

	if fakeGeo.callCount != 1 {
		t.Errorf("expected geocoder called once for 3 identical cities, got %d", fakeGeo.callCount)
	}
	if metrics.cacheMissCount != 1 {
		t.Errorf("expected 1 cache miss, got %d", metrics.cacheMissCount)
	}
	if metrics.cacheHitCount != 2 {
		t.Errorf("expected 2 cache hits, got %d", metrics.cacheHitCount)
	}
}
