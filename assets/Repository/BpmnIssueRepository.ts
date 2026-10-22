import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import BpmnIssue from '../Entity/BpmnIssue.js';

export default class BpmnIssueRepository extends AbstractApiRepository<BpmnIssue> {
  static getEntityType() {
    return BpmnIssue;
  }
}
