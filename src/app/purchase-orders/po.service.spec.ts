import { TestBed, inject } from '@angular/core/testing';

import { PoService } from './po.service';

describe('PoService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [PoService]
    });
  });

  it('should be created', inject([PoService], (service: PoService) => {
    expect(service).toBeTruthy();
  }));
});
