import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PoStatusStepsComponent } from './po-status-steps.component';

describe('PoStatusStepsComponent', () => {
  let component: PoStatusStepsComponent;
  let fixture: ComponentFixture<PoStatusStepsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PoStatusStepsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PoStatusStepsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
