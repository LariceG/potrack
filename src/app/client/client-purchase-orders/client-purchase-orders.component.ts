import { OnInit } from '@angular/core';
import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { FormArray , FormControl , FormBuilder, FormGroup , Validators } from '@angular/forms';
import {ToasterModule, ToasterService} from 'angular2-toaster';
declare var jQuery: any;
import * as myGlobals from '../../shared/globals';
import { PoService }    from '../../purchase-orders/po.service';

@Component({
  selector: 'app-client-purchase-orders',
  templateUrl: './client-purchase-orders.component.html',
  styleUrls: ['./client-purchase-orders.component.css'],
  providers:[PoService]
})

export class ClientPurchaseOrdersComponent implements OnInit
{
  private toasterService: ToasterService;
  token = {};
  Url = '';
  total;
  perpage = 10;
  page = 1;
  PurchaseOrders = [];
  expandedPo = '';
  poSelected = '';


  constructor( private router: Router , toasterService: ToasterService , private fb: FormBuilder , private activatedRoute: ActivatedRoute , private po: PoService )
  {
    this.toasterService = toasterService;
  }

  ngOnInit()
  {
    let tkn    = localStorage.getItem('AppToken');
    this.token  = JSON.parse(tkn);
    this.getPurchaseOrdes();
    this.Url = myGlobals.baseUrl;
  }

  ngAfterViewInit()
  {
    this.activatedRoute.params.subscribe((params: Params) => {
        let type = params['type'];
        if(type && type == 'add')
        {
          jQuery('#addPo').modal('show');
        }
      });
  }

  getPurchaseOrdes()
  {
    var v = {};
    v['page'] = this.page;
    v['perpage'] = this.perpage;
    if(this.token['userType'] == 1)
    {
      v['type'] = 'forSales';
    }

    else if(this.token['userType'] == 2)
    {
      v['type'] = 'forDesigner';
    }

    else if(this.token['userType'] == 3)
    {
      v['type'] = 'forProduction';
    }

    else if(this.token['userType'] == 4)
    {
      v['type'] = 'forClient';
    }

    v['userId']  = this.token['userId'];

    console.log(v);

    this.po.getPurchaseOrdes(v).subscribe(
      data => {
        if(data.success)
        {
          this.PurchaseOrders = data.data.result;
          this.total = data.data.total;
        }
      }
    );
  }

  ChangePoStatus(orderId,status,type)
  {
    if(type == 'confirm')
    {
      this.poSelected  = orderId;
      jQuery('#confirm').modal('show');
      return false;
    }

    this.po.ChangePoStatus(orderId,status).subscribe(
      data => {
        this.getPurchaseOrdes();
        this.toasterService.pop('success',data.data, '' );
        jQuery('#confirm').modal('hide');
      },
      err => {
      }
    );
  }

  expandPO(orderId)
  {
    if(this.expandedPo == orderId)
    this.expandedPo = '';
    else
    this.expandedPo = orderId;
  }

}
