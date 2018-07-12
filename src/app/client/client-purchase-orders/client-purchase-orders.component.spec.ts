import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ClientPurchaseOrdersComponent } from './client-purchase-orders.component';

describe('ClientPurchaseOrdersComponent', () => {
  let component: ClientPurchaseOrdersComponent;
  let fixture: ComponentFixture<ClientPurchaseOrdersComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ClientPurchaseOrdersComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ClientPurchaseOrdersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
