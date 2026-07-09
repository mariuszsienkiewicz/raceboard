package geocoder

import (
	"context"
	"log"
	"sync"
	"time"

	"golang.org/x/time/rate"
)

type Cache interface {
	Get(city string) (Coordinates, bool)
	Set(city string, coords Coordinates)
}

type Service struct {
	client  *Client
	cache   Cache
	workers int
	limiter *rate.Limiter
}

func NewService(client *Client, cache Cache, workers int) *Service {
	return &Service{
		client:  client,
		cache:   cache,
		workers: workers,
		limiter: rate.NewLimiter(rate.Every(time.Second), 1),
	}
}

func (s *Service) GeocodeMany(ctx context.Context, cities []string) []Coordinates {
	jobs := make(chan string)
	results := make(chan Coordinates)

	// waitgroup for workers
	var wg sync.WaitGroup

	for i := 0; i < s.workers; i++ {
		wg.Add(1)
		go s.worker(ctx, jobs, results, &wg)
	}

	go func() {
		defer close(jobs)
		for _, city := range cities {
			select {
			case jobs <- city:
			case <-ctx.Done():
				return
			}
		}
	}()

	// wait for all workers to finish
	go func() {
		wg.Wait()
		close(results)
	}()

	// collect results from results channel
	collected := make([]Coordinates, 0, len(cities))
	for coords := range results {
		collected = append(collected, coords)
	}

	return collected
}

func (s *Service) worker(ctx context.Context, jobs <-chan string, results chan<- Coordinates, wg *sync.WaitGroup) {
	defer wg.Done()

	for city := range jobs {
		if coords, found := s.cache.Get(city); found {
			log.Printf("cache hit for %q", city)
			select {
			case results <- coords:
			case <-ctx.Done():
				return
			}
			continue
		}

		if err := s.limiter.Wait(ctx); err != nil {
			log.Printf("rate limiter wait failed: %v", err)
			continue
		}

		coords, err := s.client.Geocode(city)
		if err != nil {
			log.Printf("geocoding %q failed: %v", city, err)
			continue
		}

		s.cache.Set(city, coords)
		select {
		case results <- coords:
		case <-ctx.Done():
			return
		}
	}
}
