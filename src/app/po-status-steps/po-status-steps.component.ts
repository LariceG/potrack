import { OnInit } from '@angular/core';
import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { FormArray , FormControl , FormBuilder, FormGroup , Validators } from '@angular/forms';
import {ToasterModule, ToasterService} from 'angular2-toaster';
declare var jQuery: any;
import * as myGlobals from '../shared/globals';
import { PoService }    from '../purchase-orders/po.service';

@Component({
  selector: 'app-po-status-steps',
  templateUrl: './po-status-steps.component.html',
  styleUrls: ['./po-status-steps.component.css'],
  providers:[PoService]
})


export class PoStatusStepsComponent implements OnInit {
  @Input() orderid;
  PurchaseOrderDetails = {};

  constructor( private router: Router , toasterService: ToasterService , private fb: FormBuilder , private activatedRoute: ActivatedRoute , private po: PoService )
  {

  }

  ngOnInit()
  {
    if(this.orderid != '')
    {
      this.getPurchaseOrderDetails();
    }
  }

  ngAfterViewInit()
  {
  }

  getPurchaseOrderDetails()
  {
    this.po.getPurchaseOrderDetails(this.orderid).subscribe(
      data => {
        if(data.success)
        {
          this.PurchaseOrderDetails = data.data;
        }
      }
    );
  }


}
