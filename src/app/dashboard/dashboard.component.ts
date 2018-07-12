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
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css'],
  providers:[PoService]
})


export class DashboardComponent implements OnInit {

    token;
    DashbordInfo = {};
    PurchaseOrdes = [];
    total;
    perpage = 10;
    page = 1;
    Url = '';
    private toasterService: ToasterService;
    expandedPo = '';
    PotoDelete = '';
    poToEdit = '';
    poEdit = false;
    orderSelected  = '';
    statusSelected = '';

    constructor( private router: Router , toasterService: ToasterService , private fb: FormBuilder , private activatedRoute: ActivatedRoute , private po: PoService )
    {
      this.toasterService = toasterService;
    }

    ngOnInit()
    {
      let tkn    = localStorage.getItem('AppToken');
      this.token  = JSON.parse(tkn);
      this.getDashbordInfo('portal',this.token['userType'])
      this.getPurchaseOrdes();
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

    ChangePoStatus(type,orderId,status)
    {

     if(type == 'confirm')
     {
       this.orderSelected = orderId;
       this.statusSelected = status;
       jQuery('#confirm').modal('show');
       return false;
     }
     this.toasterService.pop('info','Loading...', '' );

      this.po.ChangePoStatus(orderId,status).subscribe(
        data => {
          this.toasterService.clear();
          jQuery('#confirm').modal('hide');
          this.getPurchaseOrdes();
          this.getDashbordInfo('portal',this.token['userType']);
          this.toasterService.pop('success',data.data, '' );
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
    deletePoConfirm(orderId)
    {
      this.PotoDelete = orderId;
      jQuery('#podeletemodal').modal('show');
    }

    deletePo()
    {
      let obj = {};
      obj['type']  = 'po';
      obj['id']  = this.PotoDelete;
      this.po.delete(obj).subscribe(
        data => {
          if(data.success)
          {
            this.toasterService.pop('success', data.success , '' );
            this.getPurchaseOrdes();
            jQuery('#podeletemodal').modal('hide');
          }
        },
        err => console.log(err)
     );
    }

    editPo(orderid)
    {
      this.poToEdit = orderid;
      this.poEdit = true;
      jQuery('#poeditmodal').modal('show');
    }

    onSuccess(data)
    {
      console.log(data);
      if(data.success)
      {
        this.poToEdit = '';
        this.poEdit = false;
        this.toasterService.pop('success', data.success , '' );
        jQuery('#poeditmodal').modal('hide');
      }
      else
      {
        this.toasterService.pop('error', data.error , '' );
        jQuery('#poeditmodal').modal('hide');
      }
    }

}
