import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import BpmnPath from '../Entity/BpmnPath.js';

export default class BpmnPathRepository extends AbstractApiRepository<BpmnPath> {
  static getEntityType() {
    return BpmnPath;
  }
}
