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
  selector: 'app-client-dashboard',
  templateUrl: './client-dashboard.component.html',
  styleUrls: ['./client-dashboard.component.css'],
  providers:[PoService]
})


export class ClientDashboardComponent implements OnInit {

  token;
  DashbordInfo = {};
  PurchaseOrdes = [];
  total;
  perpage = 10;
  page = 1;
  Url = '';
  private toasterService: ToasterService;
  expandedPo = '';

  constructor( private router: Router , toasterService: ToasterService , private fb: FormBuilder , private activatedRoute: ActivatedRoute , private po: PoService )
  {
    this.toasterService = toasterService;
  }

  ngOnInit()
  {
    let tkn    = localStorage.getItem('AppToken');
    this.token  = JSON.parse(tkn);
    this.getDashbordInfo('client',this.token['userType'])
    this.getPurchaseOrdes(this.token['userId']);
    this.Url = myGlobals.baseUrl;
  }


  getDashbordInfo(type,userType)
  {
    this.po.getDashbordInfo(type,userType).subscribe(
      data => {
        if(data.success)
        {
          this.DashbordInfo = data.data;
        }
      }
    );
  }

  getPurchaseOrdes(userId)
  {
    var v         = {};
    v['page']     = this.page;
    v['perpage']  = this.perpage;
    v['userId']   = userId;

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

    this.po.getPurchaseOrdes(v).subscribe(
      data => {
        if(data.success)
        {
          this.PurchaseOrdes = data.data.result;
          this.total = data.data.total;
        }
      }
    );
  }

  ChangePoStatus(orderId,status)
  {
    this.po.ChangePoStatus(orderId,status).subscribe(
      data => {
        this.toasterService.pop('success',data.data, '' );
        this.getPurchaseOrdes(this.token['userId']);
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
