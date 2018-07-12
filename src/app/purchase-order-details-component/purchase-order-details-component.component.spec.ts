import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PurchaseOrderDetailsComponentComponent } from './purchase-order-details-component.component';

describe('PurchaseOrderDetailsComponentComponent', () => {
  let component: PurchaseOrderDetailsComponentComponent;
  let fixture: ComponentFixture<PurchaseOrderDetailsComponentComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PurchaseOrderDetailsComponentComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PurchaseOrderDetailsComponentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
