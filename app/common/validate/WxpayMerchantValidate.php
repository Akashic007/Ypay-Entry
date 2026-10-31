<?php

// 还原自 Swoole Compiler 加密文件: WxpayMerchantValidate.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/validate/WxpayMerchantValidate.php

namespace app\common\validate;

class WxpayMerchantValidate extends \think\Validate {
    protected $rule = ['contact_info' => 'require|array', 'contact_info.contact_type' => 'require|in:LEGAL,SUPER', 'contact_info.contact_name' => ['require', null, 'max:100', null, 'regex:/^[^\\s].*[^\\s]$/u'], 'contact_info.contact_id_doc_type' => 'requireIf:contact_info.contact_type,SUPER|in:IDENTIFICATION_TYPE_IDCARD,IDENTIFICATION_TYPE_OVERSEA_PASSPORT,IDENTIFICATION_TYPE_HONGKONG_PASSPORT,IDENTIFICATION_TYPE_MACAO_PASSPORT,IDENTIFICATION_TYPE_TAIWAN_PASSPORT,IDENTIFICATION_TYPE_FOREIGN_RESIDENT,IDENTIFICATION_TYPE_HONGKONG_MACAO_RESIDENT,IDENTIFICATION_TYPE_TAIWAN_RESIDENT', 'contact_info.contact_id_number' => 'requireIf:contact_info.contact_type,SUPER|max:2048', 'contact_info.contact_id_doc_copy_back' => 'length:1,2048', 'contact_info.contact_period_begin' => 'requireIf:contact_info.contact_type,SUPER|dateFormat:Y-m-d', 'contact_info.contact_period_end' => ['requireIf:contact_info.contact_type,SUPER', null], 'contact_info.openid' => 'max:2048', 'contact_info.mobile_phone' => ['require', null, 'max:2048', null], 'contact_info.contact_email' => 'require|email|max:2048', 'subject_info' => 'require|array', 'subject_info.subject_type' => 'require|in:SUBJECT_TYPE_INDIVIDUAL,SUBJECT_TYPE_ENTERPRISE,SUBJECT_TYPE_GOVERNMENT,SUBJECT_TYPE_INSTITUTIONS,SUBJECT_TYPE_OTHERS', 'subject_info.finance_institution' => 'in:true,false', 'subject_info.business_license_info' => 'requireCallback:subjectInfoBusinessLicenseInfo|array', 'subject_info.business_license_info.license_copy' => 'requireCallback:subjectInfoBusinessLicenseInfo', 'subject_info.business_license_info.license_number' => ['requireCallback:subjectInfoBusinessLicenseInfo', null], 'subject_info.business_license_info.merchant_name' => ['requireCallback:subjectInfoBusinessLicenseInfo', null, 'length:2,128', null, 'regex:/^[^\\s].*[^\\s]$/u'], 'subject_info.business_license_info.legal_person' => ['requireCallback:subjectInfoBusinessLicenseInfo', null, 'length:2,128', null, 'regex:/^[^\\s].*[^\\s]$/u'], 'subject_info.business_license_info.license_address' => ['length:4,128', null, 'regex:/^[^\\s].*[^\\s]$/u'], 'subject_info.business_license_info.period_begin' => 'dateFormat:Y-m-d|after:1900-01-01|before:today', 'subject_info.business_license_info.period_end' => ['regex:/^(长期|[0-9]{4}-[0-9]{2}-[0-9]{2})$/', null], 'subject_info.certificate_info' => 'requireCallback:subjectInfoCertificateInfo|array', 'subject_info.certificate_info.cert_copy' => 'requireCallback:subjectInfoCertificateInfo|max:2048', 'subject_info.certificate_info.cert_type' => 'requireCallback:subjectInfoCertificateInfo|in:CERTIFICATE_TYPE_2388,CERTIFICATE_TYPE_2389,CERTIFICATE_TYPE_2394,CERTIFICATE_TYPE_2395,CERTIFICATE_TYPE_2396,CERTIFICATE_TYPE_2520,CERTIFICATE_TYPE_2521,CERTIFICATE_TYPE_2522,CERTIFICATE_TYPE_2399,CERTIFICATE_TYPE_2400', 'subject_info.certificate_info.cert_number' => ['requireCallback:subjectInfoCertificateInfo', null, 'max:32'], 'subject_info.certificate_info.merchant_name' => ['requireCallback:subjectInfoCertificateInfo', null], 'subject_info.certificate_info.company_address' => ['requireCallback:subjectInfoCertificateInfo', null, 'length:4,128', null, 'regex:/^[^\\s].*[^\\s]$/u'], 'subject_info.certificate_info.legal_person' => ['requireCallback:subjectInfoCertificateInfo', null, 'length:2,100', null, 'regex:/^[^\\s].*[^\\s]$/u'], 'subject_info.certificate_info.period_begin' => 'requireCallback:subjectInfoCertificateInfo|dateFormat:Y-m-d|after:1900-01-01|before:today', 'subject_info.certificate_info.period_end' => ['requireCallback:subjectInfoCertificateInfo', null, 'regex:/^(长期|[0-9]{4}-[0-9]{2}-[0-9]{2})$/'], 'subject_info.certificate_letter_copy' => 'max:2048', 'subject_info.finance_institution_info' => 'requireIf:subject_info.finance_institution,true|array', 'subject_info.finance_institution_info.finance_type' => ['requireIf:subject_info.finance_institution,true', null], 'subject_info.finance_institution_info.finance_license_pics' => ['requireIf:subject_info.finance_institution,true', null], 'subject_info.identity_info' => 'array', 'subject_info.identity_info.id_holder_type' => 'in:LEGAL,SUPER', 'subject_info.identity_info.id_doc_type' => 'requireIf:subject_info.identity_info.id_holder_type,LEGAL|in:IDENTIFICATION_TYPE_IDCARD,IDENTIFICATION_TYPE_OVERSEA_PASSPORT,IDENTIFICATION_TYPE_HONGKONG_PASSPORT,IDENTIFICATION_TYPE_MACAO_PASSPORT,IDENTIFICATION_TYPE_TAIWAN_PASSPORT,IDENTIFICATION_TYPE_FOREIGN_RESIDENT,IDENTIFICATION_TYPE_HONGKONG_MACAO_RESIDENT,IDENTIFICATION_TYPE_TAIWAN_RESIDENT', 'subject_info.identity_info.authorize_letter_copy' => 'requireIf:subject_info.identity_info.id_holder_type,SUPER|max:2048', 'subject_info.identity_info.id_card_info' => 'requireCallback:subjectInfoIdentityInfoIdCardInfo|array', 'subject_info.identity_info.id_card_info.id_card_copy' => 'requireCallback:subjectInfoIdentityInfoIdCardInfo', 'subject_info.identity_info.id_card_info.id_card_national' => 'requireCallback:subjectInfoIdentityInfoIdCardInfo', 'subject_info.identity_info.id_card_info.id_card_name' => ['regex:/^[^\\s].*[^\\s]$/u', null, 'regex:/.*[a-zA-Z\\x{4e00}-\\x{9fa5}]+.*/u'], 'subject_info.identity_info.id_card_info.id_card_number' => 'requireCallback:subjectInfoIdentityInfoIdCardInfo', 'subject_info.identity_info.id_card_info.card_period_begin' => 'requireCallback:subjectInfoIdentityInfoIdCardInfo|dateFormat:Y-m-d|after:1900-01-01', 'subject_info.identity_info.id_card_info.card_period_end' => 'requireCallback:subjectInfoIdentityInfoIdCardInfo|dateFormat:Y-m-d|after:1900-01-01', 'subject_info.identity_info.id_doc_info' => 'requireCallback:subjectInfoIdentityInfoIdDocInfo|array', 'subject_info.identity_info.id_doc_info.id_doc_copy' => 'requireCallback:subjectInfoIdentityInfoIdDocInfo|max:2048', 'subject_info.identity_info.id_doc_info.id_doc_copy_back' => 'requireCallback:subjectInfoIdentityInfoIdDocInfo|max:2048', 'subject_info.identity_info.id_doc_info.id_doc_number' => ['requireCallback:subjectInfoIdentityInfoIdDocInfo', null], 'subject_info.identity_info.id_doc_info.id_doc_address' => ['max:128', null, 'regex:/^[^\\s].*[^\\s]$/u', null], 'subject_info.identity_info.id_doc_info.doc_period_begin' => 'requireCallback:subjectInfoIdentityInfoIdDocInfo|dateFormat:Y-m-d|after:1900-01-01|before:today', 'subject_info.identity_info.id_doc_info.doc_period_end' => ['requireCallback:subjectInfoIdentityInfoIdDocInfo', null], 'subject_info.ubo_info_list' => 'array', 'business_info' => 'require|array', 'business_info.merchant_shortname' => ['require', null], 'business_info.service_phone' => ['require', null], 'business_info.sales_info' => 'require|array', 'business_info.sales_info.sales_scenes_type' => 'require|array|businessInfoSalesInfoSalesScenesType', 'business_info.sales_info.biz_store_info.biz_store_name' => ['length:1,50', null], 'business_info.sales_info.biz_store_info.biz_address_code' => ['max:128'], 'business_info.sales_info.biz_store_info.biz_store_address' => ['length:4,512', null], 'business_info.sales_info.biz_store_info.biz_sub_appid' => ['max:256', null], 'business_info.sales_info.mp_info.mp_appid' => ['max:256', null, 'regex:/^[a-zA-Z0-9_\\-]*$/'], 'business_info.sales_info.mp_info.mp_sub_appid' => ['max:256', null, 'regex:/^[a-zA-Z0-9_\\-]*$/'], 'business_info.sales_info.mp_info.mp_pics' => ['array', null], 'business_info.sales_info.mini_program_info.mini_program_appid' => 'checkMiniProgramAppid', 'business_info.sales_info.mini_program_info.mini_program_sub_appid' => 'checkMiniProgramSubAppid', 'business_info.sales_info.mini_program_info.mini_program_pics' => 'checkMiniProgramPics', 'business_info.sales_info.app_info' => 'requireCallback:checkAppInfo|array', 'business_info.sales_info.app_info.app_appid' => 'checkAppAppid', 'business_info.sales_info.app_info.app_sub_appid' => 'checkAppSubAppid', 'business_info.sales_info.app_info.app_pics' => 'requireCallback:checkAppInfo|checkAppPics', 'business_info.sales_info.web_info' => 'requireCallback:checkWebInfo|array', 'business_info.sales_info.web_info.domain' => 'checkWebDomain', 'business_info.sales_info.web_info.web_authorisation' => 'checkWebAuthorisation', 'business_info.sales_info.web_info.web_appid' => 'checkWebAppid', 'business_info.sales_info.wework_info' => 'requireCallback:checkWeworkInfo|array', 'business_info.sales_info.wework_info.sub_corp_id' => 'requireCallback:checkWeworkInfo|checkWeworkSubCorpId', 'business_info.sales_info.wework_info.wework_pics' => 'requireCallback:checkWeworkInfo|checkWeworkPics', 'settlement_info' => 'require|array', 'settlement_info.settlement_id' => 'require', 'settlement_info.qualification_type' => 'require', 'settlement_info.qualifications' => 'max:2048', 'settlement_info.activities_id' => 'max:32', 'settlement_info.activities_rate' => 'max:32', 'settlement_info.activities_additions' => 'array', 'settlement_info.debit_activities_rate' => 'max:32', 'settlement_info.credit_activities_rate' => 'max:32', 'bank_account_info' => 'require|array', 'bank_account_info.bank_account_type' => 'require|in:BANK_ACCOUNT_TYPE_CORPORATE,BANK_ACCOUNT_TYPE_PERSONAL', 'bank_account_info.account_name' => 'require|max:2048', 'bank_account_info.account_bank' => 'max:128', 'bank_account_info.bank_address_code' => 'max:128', 'bank_account_info.bank_branch_id' => 'max:128', 'bank_account_info.bank_name' => 'max:128', 'bank_account_info.account_number' => 'require|max:2048', 'addition_info.legal_person_commitment' => 'max:1024', 'addition_info.legal_person_video' => 'max:1024', 'addition_info.business_addition_pics' => 'array', 'addition_info.business_addition_msg' => 'max:512'];
    protected $message = ['contact_info.require' => '超级管理员信息不能为空', 'contact_info.array' => '超级管理员信息格式错误', 'contact_info.contact_type.require' => '超级管理员类型不能为空', 'contact_info.contact_type.in' => '超级管理员类型必须是 LEGAL 或 SUPER', 'contact_info.contact_name.require' => '超级管理员姓名不能为空', 'contact_info.contact_name.max' => '超级管理员姓名不能超过100个字符', 'contact_info.contact_name.regex' => '超级管理员姓名格式不正确', 'contact_info.contact_id_doc_type.requireIf' => '超级管理员证件类型不能为空', 'contact_info.contact_id_doc_type.in' => '超级管理员证件类型不合法', 'contact_info.contact_id_number.requireIf' => '超级管理员证件号码不能为空', 'contact_info.contact_id_number.max' => '超级管理员证件号码不能超过2048个字符', 'contact_info.contact_id_doc_copy.requireIf' => '请上传超级管理员证件正面', 'contact_info.contact_id_doc_copy.length' => '超级管理员证件正面格式不正确', 'contact_info.contact_id_doc_copy_back.length' => '超级管理员证件反面格式不正确', 'contact_info.contact_period_begin.requireIf' => '超级管理员证件有效期开始时间不能为空', 'contact_info.contact_period_begin.dateFormat' => '超级管理员证件有效期开始时间格式错误', 'contact_info.contact_period_end.requireIf' => '超级管理员证件有效期结束时间不能为空', 'contact_info.contact_period_end.regex' => '超级管理员证件有效期结束时间格式不正确', 'contact_info.openid.max' => 'OpenID 不能超过2048个字符', 'contact_info.mobile_phone.require' => '联系手机号不能为空', 'contact_info.mobile_phone.max' => '联系手机号不能超过2048个字符', 'contact_info.mobile_phone.regex' => '联系手机号格式不正确', 'contact_info.contact_email.require' => '联系邮箱不能为空', 'contact_info.contact_email.email' => '联系邮箱格式不正确', 'contact_info.contact_email.max' => '联系邮箱不能超过2048个字符', 'subject_info.require' => '主体资料不能为空', 'subject_info.array' => '主体资料格式错误', 'subject_info.subject_type.require' => '主体类型不能为空', 'subject_info.subject_type.in' => '主体类型不合法', 'subject_info.finance_institution.boolean' => '是否金融机构必须为布尔值', 'subject_info.business_license_info.requireCallback' => '营业执照信息不能为空', 'subject_info.business_license_info.array' => '营业执照信息格式错误', 'subject_info.business_license_info.license_copy.requireCallback' => '营业执照照片不能为空', 'subject_info.business_license_info.license_number.requireCallback' => '注册号/统一社会信用代码不能为空', 'subject_info.business_license_info.license_number.regex' => '注册号/统一社会信用代码格式不正确', 'subject_info.business_license_info.merchant_name.requireCallback' => '商户名称不能为空', 'subject_info.business_license_info.merchant_name.length' => '商户名称长度必须在2-128个字符之间', 'subject_info.business_license_info.legal_person.requireCallback' => '法定代表人姓名不能为空', 'subject_info.business_license_info.legal_person.length' => '法定代表人姓名长度必须在2-128个字符之间', 'subject_info.business_license_info.legal_person.regex' => '法定代表人姓名格式不正确', 'subject_info.business_license_info.license_address.length' => '注册地址长度必须在4-128个字符之间', 'subject_info.business_license_info.license_address.regex' => '注册地址格式不正确', 'subject_info.business_license_info.period_begin.dateFormat' => '营业执照有效期开始时间格式错误', 'subject_info.business_license_info.period_begin.after' => '营业执照有效期开始时间不能早于1900-01-01', 'subject_info.business_license_info.period_begin.before' => '营业执照有效期开始时间不能晚于今天', 'subject_info.business_license_info.period_end.regex' => '营业执照有效期结束时间格式不正确', 'subject_info.business_license_info.period_end.after' => '营业执照有效期结束时间必须晚于开始日期', 'subject_info.certificate_info.requireCallback' => '登记证书信息不能为空', 'subject_info.certificate_info.array' => '登记证书信息格式错误', 'subject_info.certificate_info.cert_copy.requireCallback' => '登记证书照片不能为空', 'subject_info.certificate_info.cert_copy.max' => '登记证书照片过长', 'subject_info.certificate_info.cert_type.requireCallback' => '登记证书类型不能为空', 'subject_info.certificate_info.cert_type.in' => '登记证书类型不合法', 'subject_info.certificate_info.cert_number.requireCallback' => '登记证书号不能为空', 'subject_info.certificate_info.cert_number.max' => '登记证书号不能超过32个字符', 'subject_info.certificate_info.cert_number.regex' => '登记证书号格式不正确', 'subject_info.certificate_info.merchant_name.requireCallback' => '登记证书商户名称不能为空', 'subject_info.certificate_info.merchant_name.length' => '登记证书商户名称长度必须在2-128个字符之间', 'subject_info.certificate_info.merchant_name.regex' => '登记证书商户名称格式不正确', 'subject_info.certificate_info.company_address.requireCallback' => '注册地址不能为空', 'subject_info.certificate_info.company_address.length' => '注册地址长度必须在4-128个字符之间', 'subject_info.certificate_info.company_address.regex' => '注册地址格式不正确', 'subject_info.certificate_info.legal_person.requireCallback' => '法定代表人不能为空', 'subject_info.certificate_info.legal_person.length' => '法定代表人长度必须在2-100个字符之间', 'subject_info.certificate_info.legal_person.regex' => '法定代表人格式不正确', 'subject_info.certificate_info.period_begin.requireCallback' => '登记证书有效期开始日期不能为空', 'subject_info.certificate_info.period_begin.dateFormat' => '登记证书有效期开始日期格式错误', 'subject_info.certificate_info.period_begin.after' => '登记证书有效期开始日期不能早于1900-01-01', 'subject_info.certificate_info.period_begin.before' => '登记证书有效期开始日期不能晚于今天', 'subject_info.certificate_info.period_end.requireCallback' => '登记证书有效期结束日期不能为空', 'subject_info.certificate_info.period_end.regex' => '登记证书有效期结束日期格式不正确', 'subject_info.certificate_info.period_end.after' => '登记证书有效期结束日期必须晚于开始日期', 'subject_info.certificate_letter_copy.max' => '单位证明函过长', 'subject_info.finance_institution_info.requireIf' => '金融机构许可证信息不能为空', 'subject_info.finance_institution_info.array' => '金融机构许可证信息格式错误', 'subject_info.finance_institution_info.finance_type.requireIf' => '金融机构类型不能为空', 'subject_info.finance_institution_info.finance_type.in' => '金融机构类型不合法', 'subject_info.finance_institution_info.finance_license_pics.requireIf' => '金融机构许可证图片不能为空', 'subject_info.finance_institution_info.finance_license_pics.array' => '金融机构许可证图片必须是数组', 'subject_info.identity_info.require' => '证件信息不能为空', 'subject_info.identity_info.array' => '证件信息格式错误', 'subject_info.identity_info.id_holder_type.in' => '证件持有人类型不合法', 'subject_info.identity_info.id_doc_type.requireIf' => '证件类型不能为空', 'subject_info.identity_info.id_doc_type.in' => '证件类型不合法', 'subject_info.identity_info.authorize_letter_copy.requireIf' => '法定代表人说明函不能为空', 'subject_info.identity_info.authorize_letter_copy.max' => '法定代表人说明函过长', 'subject_info.identity_info.id_card_info.requireCallback' => '身份证信息不能为空', 'subject_info.identity_info.id_card_info.array' => '身份证信息格式错误', 'subject_info.identity_info.id_card_info.id_card_copy.requireCallback' => '身份证人像面照片不能为空', 'subject_info.identity_info.id_card_info.id_card_national.requireCallback' => '身份证国徽面照片不能为空', 'subject_info.identity_info.id_card_info.id_card_name.requireCallback' => '身份证姓名不能为空', 'subject_info.identity_info.id_card_info.id_card_name.length' => '身份证姓名长度必须在2-100个字符之间', 'subject_info.identity_info.id_card_info.id_card_name.regex' => '身份证姓名格式不正确', 'subject_info.identity_info.id_card_info.id_card_number.requireCallback' => '身份证号码不能为空', 'subject_info.identity_info.id_card_info.id_card_address.requireCallback' => '身份证地址不能为空', 'subject_info.identity_info.id_card_info.id_card_address.max' => '身份证地址不能超过128个字符', 'subject_info.identity_info.id_card_info.card_period_begin.requireCallback' => '身份证有效期开始时间不能为空', 'subject_info.identity_info.id_card_info.card_period_begin.dateFormat' => '身份证有效期开始时间格式错误', 'subject_info.identity_info.id_card_info.card_period_begin.after' => '身份证有效期开始时间不能早于1900-01-01', 'subject_info.identity_info.id_card_info.card_period_end.requireCallback' => '身份证有效期结束时间不能为空', 'subject_info.identity_info.id_card_info.card_period_end.dateFormat' => '身份证有效期结束时间格式错误', 'subject_info.identity_info.id_card_info.card_period_end.after' => '身份证有效期结束时间不能早于1900-01-01', 'subject_info.identity_info.id_doc_info.requireCallback' => '其他证件信息不能为空', 'subject_info.identity_info.id_doc_info.array' => '其他证件信息格式错误', 'subject_info.identity_info.id_doc_info.id_doc_copy.requireCallback' => '证件正面照片不能为空', 'subject_info.identity_info.id_doc_info.id_doc_copy.max' => '证件正面照片过长', 'subject_info.identity_info.id_doc_info.id_doc_copy_back.requireCallback' => '证件反面照片不能为空', 'subject_info.identity_info.id_doc_info.id_doc_copy_back.max' => '证件反面照片过长', 'subject_info.identity_info.id_doc_info.id_doc_number.requireCallback' => '证件号码不能为空', 'subject_info.identity_info.id_doc_info.id_doc_number.regex' => '证件号码格式不正确', 'subject_info.identity_info.id_doc_info.id_doc_address.max' => '证件地址不能超过128个字符', 'subject_info.identity_info.id_doc_info.id_doc_address.regex' => '证件地址格式不正确', 'subject_info.identity_info.id_doc_info.doc_period_begin.requireCallback' => '证件有效期开始时间不能为空', 'subject_info.identity_info.id_doc_info.doc_period_begin.dateFormat' => '证件有效期开始时间格式错误', 'subject_info.identity_info.id_doc_info.doc_period_begin.after' => '证件有效期开始时间不能早于1900-01-01', 'subject_info.identity_info.id_doc_info.doc_period_begin.before' => '证件有效期开始时间不能晚于今天', 'subject_info.identity_info.id_doc_info.doc_period_end.requireCallback' => '证件有效期结束时间不能为空', 'subject_info.identity_info.id_doc_info.doc_period_end.regex' => '证件有效期结束时间格式不正确', 'subject_info.ubo_info_list.array' => 'UBO 信息格式错误', 'business_info.require' => '经营资料不能为空', 'business_info.array' => '经营资料格式错误', 'business_info.merchant_shortname.require' => '商户简称不能为空', 'business_info.merchant_shortname.length' => '商户简称长度必须在1-64个字符之间', 'business_info.service_phone.require' => '客服电话不能为空', 'business_info.service_phone.regex' => '客服电话格式不正确', 'business_info.sales_info.require' => '场景信息不能为空', 'business_info.sales_info.array' => '场景信息格式错误', 'business_info.sales_info.sales_scenes_type.require' => '经营场所类型不能为空', 'business_info.sales_info.sales_scenes_type.array' => '经营场所类型必须是数组', 'business_info.sales_info.sales_scenes_type.businessInfoSalesInfoSalesScenesType' => '经营场所类型不合法', 'business_info.sales_info.biz_store_info.requireCallback' => '线下场所信息不能为空', 'business_info.sales_info.biz_store_info.array' => '线下场所信息格式错误', 'business_info.sales_info.biz_store_info.biz_store_name.requireCallback' => '门店名称不能为空', 'business_info.sales_info.biz_store_info.biz_store_name.length' => '门店名称长度必须在1-50个字符之间', 'business_info.sales_info.biz_store_info.biz_store_name.regex' => '门店名称格式不正确', 'business_info.sales_info.biz_store_info.biz_address_code.requireCallback' => '省市编码不能为空', 'business_info.sales_info.biz_store_info.biz_store_address.requireCallback' => '详细地址不能为空', 'business_info.sales_info.biz_store_info.biz_store_address.length' => '详细地址长度必须在4-512个字符之间', 'business_info.sales_info.biz_store_info.biz_store_address.regex' => '详细地址格式不正确', 'business_info.sales_info.biz_store_info.store_entrance_pic.requireCallback' => '门店照片不能为空', 'business_info.sales_info.biz_store_info.store_entrance_pic.max' => '门店照片过长', 'business_info.sales_info.biz_store_info.indoor_pic.requireCallback' => '内部照片不能为空', 'business_info.sales_info.biz_store_info.indoor_pic.max' => '内部照片过长', 'business_info.sales_info.biz_store_info.biz_sub_appid.max' => 'AppID 不能超过256个字符', 'business_info.sales_info.biz_store_info.biz_sub_appid.regex' => 'AppID 格式不正确', 'business_info.sales_info.mp_info.requireCallback' => '公众号信息不能为空', 'business_info.sales_info.mp_info.array' => '公众号信息格式错误', 'business_info.sales_info.mp_info.mp_appid.max' => '服务商公众号AppID不能超过256个字符', 'business_info.sales_info.mp_info.mp_appid.regex' => '服务商公众号AppID格式不正确', 'business_info.sales_info.mp_info.mp_appid.businessInfoSalesInfoMpInfoMpAppid' => '服务商公众号AppID校验失败', 'business_info.sales_info.mp_info.mp_sub_appid.max' => '商家公众号AppID不能超过256个字符', 'business_info.sales_info.mp_info.mp_sub_appid.regex' => '商家公众号AppID格式不正确', 'business_info.sales_info.mp_info.mp_sub_appid.businessInfoSalesInfoMpSubAppid' => '商家公众号AppID校验失败', 'business_info.sales_info.mp_info.mp_pics.requireCallback' => '公众号页面截图不能为空', 'business_info.sales_info.mp_info.mp_pics.array' => '公众号页面截图必须是数组', 'business_info.sales_info.mp_info.mp_pics.businessInfoSalesInfoMpInfoMpPics' => '公众号页面截图校验失败', 'business_info.sales_info.mini_program_info.requireCallback' => '小程序信息不能为空', 'business_info.sales_info.mini_program_info.array' => '小程序信息格式错误', 'business_info.sales_info.mini_program_info.mini_program_appid.checkMiniProgramAppid' => '小程序AppID校验失败', 'business_info.sales_info.mini_program_info.mini_program_sub_appid.checkMiniProgramSubAppid' => '商家小程序AppID校验失败', 'business_info.sales_info.mini_program_info.mini_program_pics.checkMiniProgramPics' => '小程序截图校验失败', 'business_info.sales_info.app_info.requireCallback' => 'App信息不能为空', 'business_info.sales_info.app_info.array' => 'App信息格式错误', 'business_info.sales_info.app_info.app_appid.checkAppAppid' => '服务商AppID校验失败', 'business_info.sales_info.app_info.app_sub_appid.checkAppSubAppid' => '商家AppID校验失败', 'business_info.sales_info.app_info.app_pics.requireCallback' => '请上传APP截图', 'business_info.sales_info.web_info.requireCallback' => '请填写互联网网站信息', 'business_info.sales_info.web_info.array' => '互联网网站信息格式错误', 'business_info.sales_info.web_info.domain.checkWebDomain' => '网站域名格式不正确', 'business_info.sales_info.web_info.web_authorisation.checkWebAuthorisation' => '请上传网站授权函', 'business_info.sales_info.web_info.web_appid.checkWebAppid' => '商家网站AppID不合法', 'business_info.sales_info.wework_info.requireCallback' => '请填写企业微信信息', 'business_info.sales_info.wework_info.array' => '企业微信信息格式错误', 'business_info.sales_info.wework_info.sub_corp_id.requireCallback' => '请填写企业微信CorpID', 'business_info.sales_info.wework_info.sub_corp_id.checkWeworkSubCorpId' => '企业微信CorpID格式不正确', 'business_info.sales_info.wework_info.wework_pics.requireCallback' => '请上传企业微信页面截图', 'business_info.sales_info.wework_info.wework_pics.checkWeworkPics' => '企业微信截图格式不正确', 'settlement_info.require' => '结算规则不能为空', 'settlement_info.array' => '结算规则格式错误', 'settlement_info.settlement_id.require' => '结算规则ID不能为空', 'settlement_info.qualification_type.require' => '所属行业不能为空', 'settlement_info.qualifications.max' => '特殊资质图片地址不能超过2048字符', 'settlement_info.activities_id.max' => '优惠费率活动ID不能超过32字符', 'settlement_info.activities_additions.array' => '优惠费率活动补充材料格式错误', 'bank_account_info.require' => '结算银行账户信息不能为空', 'bank_account_info.array' => '结算银行账户信息格式错误', 'bank_account_info.bank_account_type.require' => '账户类型不能为空', 'bank_account_info.bank_account_type.in' => '账户类型不合法（必须为对公或对私）', 'bank_account_info.account_name.require' => '开户名称不能为空', 'bank_account_info.account_name.max' => '开户名称不能超过2048字符', 'bank_account_info.account_bank.require' => '开户银行不能为空', 'bank_account_info.account_bank.max' => '开户银行不能超过128字符', 'bank_account_info.bank_address_code.max' => '开户银行省市编码不能超过128字符', 'bank_account_info.bank_branch_id.max' => '开户银行号不能超过128字符', 'bank_account_info.bank_name.max' => '开户银行全称不能超过128字符', 'bank_account_info.account_number.require' => '银行账号不能为空', 'bank_account_info.account_number.max' => '银行账号不能超过2048字符', 'addition_info.legal_person_commitment.max' => '法定代表人开户承诺函地址不能超过1024字符', 'addition_info.legal_person_video.max' => '法定代表人开户意愿视频地址不能超过1024字符', 'addition_info.business_addition_pics.array' => '补充材料必须为数组格式', 'addition_info.business_addition_msg.max' => '补充说明不能超过512字符'];
    protected function subjectinfoidentityinfoidcardinfo($value, $data): bool
    {
    // IS_EQUAL $Sh44285['subject_info']['identity_info']['id_holder_type'] 'LEGAL'
    // IS_EQUAL $Sh44285['subject_info']['identity_info']['id_doc_type'] 'IDENTIFICATION_TYPE_IDCARD'
    return true;
    return false;
    }

    protected function subjectinfobusinesslicenseinfo($value, $data): bool
    {
    // IS_EQUAL $Sh44287['subject_info']['subject_type'] 'SUBJECT_TYPE_INDIVIDUAL'
    // IS_EQUAL $Sh44287['subject_info']['subject_type'] 'SUBJECT_TYPE_ENTERPRISE'
    return true;
    return false;
    }

    protected function subjectinfocertificateinfo($value, $data): bool
    {
    // IS_EQUAL $Sh44289['subject_info']['subject_type'] 'SUBJECT_TYPE_GOVERNMENT'
    // IS_EQUAL $Sh44289['subject_info']['subject_type'] 'SUBJECT_TYPE_INSTITUTIONS'
    // IS_EQUAL $Sh44289['subject_info']['subject_type'] 'SUBJECT_TYPE_OTHERS'
    return true;
    return false;
    }

    protected function subjectinfoidentityinfoiddocinfo($value, $data): bool
    {
    // IS_EQUAL $Sh44291['subject_info']['identity_info']['id_holder_type'] 'LEGAL'
    // IS_NOT_EQUAL $Sh44291['subject_info']['identity_info']['id_doc_type'] 'IDENTIFICATION_TYPE_IDCARD'
    return true;
    return false;
    }

    protected function subjectinfouboinfolist($value, $data): bool
    {
    // IS_EQUAL $Sh44293['subject_info']['subject_type'] 'SUBJECT_TYPE_ENTERPRISE'
    return true;
    return false;
    }

    protected function businessinfosalesinfosalesscenestype($value, $data): bool|string
    {
    // [FE_RESET_R] $Sh44294 
    return '经营场景类型包含不允许的值';
    return true;
    }

    protected function checkbizstoreinfo($value, $data): bool|string
    {
    return '选择线下场所场景时，线下场所信息必填';
    return true;
    }

    protected function checkmpinfo($value, $data): bool|string
    {
    return '选择服务号/公众号场景时，mp_info 必填';
    return true;
    }

    protected function checkminiprograminfo($value, $data): bool|string
    {
    return '选择小程序场景时，mini_program_info 必填';
    return true;
    }

    protected function checkappinfo($value, $data): bool|string
    {
    return true;
    return false;
    }

    protected function checkwebinfo($value, $data): bool|string
    {
    return true;
    return false;
    }

    protected function checkweworkinfo($value, $data): bool|string
    {
    return true;
    return false;
    }

    protected function businessinfosalesinfompinfompappid($value, $data): bool|string
    {
    return '服务商服务号或公众号AppID、商家服务号或公众号AppID，二选一必填';
    return true;
    }

    protected function businessinfosalesinfompsubappid($value, $data): bool|string
    {
    return '服务商服务号或公众号AppID、商家服务号或公众号AppID，二选一必填';
    return true;
    }

    protected function businessinfosalesinfompinfomppics($value, $data): bool|string
    {
    return '服务号/公众号页面截图最多5张';
    // [FE_RESET_R] $Sh44322 
    return '服务号/公众号页面截图 MediaID 长度不能超过 2048';
    return true;
    }

    protected function checkminiprogramappid($value, $data): bool|string
    {
    return '服务商小程序AppID与商家小程序AppID，二选一必填';
    return '服务商小程序AppID格式不正确，最长256字符，只允许字母、数字、下划线、横线';
    return true;
    }

    protected function checkminiprogramsubappid($value, $data): bool|string
    {
    return '服务商小程序AppID与商家小程序AppID，二选一必填';
    return '商家小程序AppID格式不正确，最长256字符，只允许字母、数字、下划线、横线';
    return true;
    }

    protected function checkminiprogrampics($value, $data): bool|string
    {
    return '小程序截图必须为数组';
    return '小程序截图最多5张';
    // [FE_RESET_R] $Sh44331 
    return '小程序截图 MediaID 长度不能超过2048';
    return true;
    }

    protected function checkappappid($value, $data): bool|string
    {
    return '服务商应用AppID与商家应用AppID，二选一必填';
    return '服务商应用AppID格式不正确，最长256字符，只允许字母、数字、下划线、横线';
    return true;
    }

    protected function checkappsubappid($value, $data): bool|string
    {
    return '服务商应用AppID与商家应用AppID，二选一必填';
    return '商家应用AppID格式不正确，最长256字符，只允许字母、数字、下划线、横线';
    return true;
    }

    protected function checkapppics($value, $data): bool|string
    {
    return 'App截图必须为数组';
    // IS_NOT_IDENTICAL null 4
    return 'App截图必须包含4张图片（首页、尾页、应用内、支付页）';
    // [FE_RESET_R] $Sh44340 
    return 'App截图 MediaID 长度不能超过1024';
    return true;
    }

    protected function checkwebdomain($value, $data): bool|string
    {
    return '互联网网站域名必填';
    return '互联网网站域名最长1024字符';
    return '互联网网站域名格式不正确';
    return true;
    }

    protected function checkwebauthorisation($value, $data): bool|string
    {
    return true;
    return '网站授权函 MediaID 最长1024字符';
    return true;
    }

    protected function checkwebappid($value, $data): bool|string
    {
    return true;
    return '互联网网站对应的商家AppID格式不正确，最长256字符，只允许字母、数字、下划线、横线';
    return true;
    }

    protected function checkweworksubcorpid($value, $data): bool|string
    {
    return '商家企业微信CorpID必填';
    return '商家企业微信CorpID格式不正确，最长256字符，只允许字母、数字、下划线、横线';
    return true;
    }

    protected function checkweworkpics($value, $data): bool|string
    {
    return '企业微信页面截图必须为数组';
    return '企业微信页面截图至少上传1张';
    return '企业微信页面截图最多上传5张';
    // [FE_RESET_R] $Sh44351 
    return '企业微信页面截图 MediaID 长度不能超过1024';
    return true;
    }

}
