import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterModule, Routes } from '@angular/router';
import { ReactiveFormsModule } from '@angular/forms';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {ToasterModule, ToasterService} from 'angular2-toaster';
import { LoadersCssModule } from 'angular2-loaders-css';
import { AuthGuard } from './shared/authGuard.service';


import { AppComponent } from './app.component';
import { LoginComponent } from './login/login.component';
import { HeaderComponent } from './header/header.component';
import { HomeComponent } from './home/home.component';
import { CompComponent } from './comp/comp.component';
import { ContactComponent } from './contact/contact.component';
import { HttpModule, JsonpModule } from '@angular/http';
import { DashboardComponent } from './dashboard/dashboard.component';
import { SidebarComponent } from './sidebar/sidebar.component';
import { NavbarComponent } from './navbar/navbar.component';
import { PurchaseOrdersComponent } from './purchase-orders/purchase-orders.component';
import { PurchaseOrderDetailsComponent } from './purchase-order-details/purchase-order-details.component';
import { PurchaseOrderDetailsComponentComponent } from './purchase-order-details-component/purchase-order-details-component.component';
import { ClientDashboardComponent } from './client/client-dashboard/client-dashboard.component';
import { ClientHeaderComponent } from './client/client-header/client-header.component';
import { ClientSidebarComponent } from './client/client-sidebar/client-sidebar.component';
import { ClientPurchaseOrdersComponent } from './client/client-purchase-orders/client-purchase-orders.component';
import {MatStepperModule} from '@angular/material/stepper';
import { PoStatusStepsComponent } from './po-status-steps/po-status-steps.component';
import { PoEditComponent } from './po-edit/po-edit.component';
import { ClientProfileComponent } from './client-profile/client-profile.component';


const appRoutes: Routes = [
  {
    path: '',
    component: LoginComponent,
    data: { title: 'Dibcase | Login' }
  },
  {
    path: 'login',
    component: LoginComponent,
    data: { title: 'Dibcase | Login' }
  },
  {
    path: 'home',
    component: HomeComponent,
    data: { title: 'Dibcase | Login' }
  },
  {
    path: 'com',
    component: CompComponent,
    data: { title: 'Dibcase | Login' }
  },
  {
    path: 'contact',
    component:ContactComponent,
    data: { title: 'Dibcase | Login' }
  },
  { path: 'dashboard', component: DashboardComponent   , canActivate: [AuthGuard] },
  { path: 'purchase-orders', component: PurchaseOrdersComponent   , canActivate: [AuthGuard] },
  { path: 'purchase-orders/:type', component: PurchaseOrdersComponent   , canActivate: [AuthGuard] },
  { path: 'dashboard/client-profile/:cl', component: ClientProfileComponent   , canActivate: [AuthGuard] },
  { path: 'purchase-order-details/:orderid', component: PurchaseOrderDetailsComponent   , canActivate: [AuthGuard] },
  { path: 'client-dashboard', component: ClientDashboardComponent   , canActivate: [AuthGuard] },
  { path: 'client-dashboard/purchase-orders', component: ClientPurchaseOrdersComponent   , canActivate: [AuthGuard] },

];

@NgModule({
  declarations: [
    AppComponent,
    LoginComponent,
    HeaderComponent,
    HomeComponent,
    CompComponent,
    ContactComponent,
    DashboardComponent,
    SidebarComponent,
    NavbarComponent,
    PurchaseOrdersComponent,
    PurchaseOrderDetailsComponent,
    PurchaseOrderDetailsComponentComponent,
    ClientDashboardComponent,
    ClientHeaderComponent,
    ClientSidebarComponent,
    ClientPurchaseOrdersComponent,
    PoStatusStepsComponent,
    PoEditComponent,
    ClientProfileComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    RouterModule.forRoot(appRoutes, { useHash: true }),
    ReactiveFormsModule,
    HttpModule,
    JsonpModule,
    BrowserAnimationsModule, ToasterModule,
    LoadersCssModule,
    MatStepperModule
  ],
  providers: [AuthGuard],
  bootstrap: [AppComponent]
})
export class AppModule { }
