package cache_test

import (
	"fmt"
	"raceboard/geocoder/internal/cache"
	"raceboard/geocoder/internal/domain"
	"sync"
	"testing"
)

func TestCache_Get_Empty(t *testing.T) {
	c := cache.New()

	_, found := c.Get("Warszawa")

	if found {
		t.Errorf("expected found=false on empty cache, got true")
	}
}

func TestCache_SetThenGet(t *testing.T) {
	c := cache.New()
	want := domain.Coordinates{City: "Warszawa", Lat: 52.23, Lng: 21.01}

	c.Set("Warszawa", want)

	got, found := c.Get("Warszawa")
	if !found {
		t.Fatalf("expected found=true after Set, got false")
	}
	if got != want {
		t.Errorf("got %+v, want %+v", got, want)
	}
}

func TestCache_Get_Miss(t *testing.T) {
	c := cache.New()
	c.Set("Warszawa", domain.Coordinates{City: "Warszawa", Lat: 52.23, Lng: 21.01})

	_, found := c.Get("Kraków")

	if found {
		t.Errorf("expected found=false for city not in cache, got true")
	}
}

func TestCache_ConcurrentAccess(t *testing.T) {
	c := cache.New()

	var wg sync.WaitGroup
	const goroutines = 100

	for i := 0; i < goroutines; i++ {
		wg.Add(1)
		go func(n int) {
			defer wg.Done()

			city := fmt.Sprintf("City%d", n)
			coords := domain.Coordinates{City: city, Lat: float64(n), Lng: float64(n)}

			c.Set(city, coords)
			got, found := c.Get(city)

			if !found {
				t.Errorf("city %q not found right after Set", city)
				return
			}
			if got != coords {
				t.Errorf("city %q: got %+v, want %+v", city, got, coords)
			}
		}(i)
	}

	wg.Wait()
}
