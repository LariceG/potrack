import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PoEditComponent } from './po-edit.component';

describe('PoEditComponent', () => {
  let component: PoEditComponent;
  let fixture: ComponentFixture<PoEditComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PoEditComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PoEditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
